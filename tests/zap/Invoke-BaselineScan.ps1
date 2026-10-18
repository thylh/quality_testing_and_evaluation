param(
    [string]$TargetUrl = 'http://127.0.0.1:8017',
    [string]$OutputDirectory = (Join-Path $PSScriptRoot 'reports'),
    [string]$ZapPath,
    [ValidateRange(1, 10)]
    [int]$SpiderMinutes = 2
)

$ErrorActionPreference = 'Stop'

try {
    $target = [System.Uri]$TargetUrl
}
catch {
    throw 'TargetUrl must be an absolute HTTP URL on localhost, 127.0.0.1, or ::1.'
}

$loopbackHosts = @('localhost', '127.0.0.1', '::1')
if (
    -not $target.IsAbsoluteUri -or
    $target.Scheme -ne 'http' -or
    $target.Host -notin $loopbackHosts -or
    $target.Port -ne 8017 -or
    $target.AbsolutePath -ne '/' -or
    $target.Query -or
    $target.Fragment -or
    $target.UserInfo
) {
    throw 'Only the HTTP loopback site root on port 8017 is allowed. Remote hosts, other ports, paths, queries, and credentials are rejected.'
}

$zapCandidates = @()
if ($ZapPath) {
    $zapCandidates += $ZapPath
}

$zapHome = $env:ZAP_HOME
if ($zapHome) {
    $zapCandidates += (Join-Path $zapHome 'zap.bat')
}

$programFiles = [Environment]::GetFolderPath('ProgramFiles')
$programFilesX86 = [Environment]::GetFolderPath('ProgramFilesX86')
$localPrograms = Join-Path $env:LOCALAPPDATA 'Programs'
$zapCandidates += @(
    (Join-Path $programFiles 'ZAP\Zed Attack Proxy\zap.bat'),
    (Join-Path $programFilesX86 'ZAP\Zed Attack Proxy\zap.bat'),
    (Join-Path $localPrograms 'ZAP\Zed Attack Proxy\zap.bat')
)

$userZapDirectory = Join-Path $localPrograms 'OWASP-ZAP'
if (Test-Path -LiteralPath $userZapDirectory -PathType Container) {
    $zapCandidates += Get-ChildItem -LiteralPath $userZapDirectory -Filter 'zap.bat' -File -Recurse -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending |
        ForEach-Object FullName
}

$zapCommand = Get-Command zap.bat -ErrorAction SilentlyContinue
if ($zapCommand) {
    $zapCandidates += $zapCommand.Source
}

$zapExecutable = $zapCandidates |
    Where-Object { $_ -and (Test-Path -LiteralPath $_ -PathType Leaf) } |
    Select-Object -First 1
if (-not $zapExecutable) {
    throw 'ZAP Desktop was not found. Install OWASP ZAP for Windows, set ZAP_HOME to its install folder, or pass -ZapPath with the full path to zap.bat.'
}

$zapExecutable = (Resolve-Path -LiteralPath $zapExecutable).Path

$targetBaseUrl = $target.GetLeftPart([System.UriPartial]::Authority)
$spiderUrl = "$targetBaseUrl/home"
$targetProbeUrl = "$spiderUrl"
$excludedPaths = @(
    "$targetBaseUrl/(login|register|logout)([/?].*)?"
    "$targetBaseUrl/(checkout|addtocart)(/.*)?"
    "$targetBaseUrl/cart/(update|remove)(/.*)?"
) -join "`n        - "

$ErrorActionPreference = 'Continue'
& curl.exe --fail --silent --show-error --output NUL --max-time 10 $targetProbeUrl *> $null
$targetExitCode = $LASTEXITCODE
$ErrorActionPreference = 'Stop'
if ($targetExitCode -ne 0) {
    throw "The isolated target is not responding at $TargetUrl. Start it with Start-IsolatedTarget.ps1."
}

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
if ([System.IO.Path]::IsPathRooted($OutputDirectory)) {
    $reportsPath = [System.IO.Path]::GetFullPath($OutputDirectory)
}
else {
    $reportsPath = [System.IO.Path]::GetFullPath((Join-Path $repoRoot $OutputDirectory))
}
New-Item -ItemType Directory -Path $reportsPath -Force | Out-Null
$htmlReport = Join-Path $reportsPath 'zap-baseline.html'
$jsonReport = Join-Path $reportsPath 'zap-baseline.json'
Remove-Item -LiteralPath $htmlReport, $jsonReport -Force -ErrorAction SilentlyContinue

$reportDirectory = $reportsPath.Replace('\', '/').Replace("'", "''")
$planPath = Join-Path $env:TEMP ("zap-baseline-{0}.yaml" -f [guid]::NewGuid())
$plan = @"
env:
  contexts:
    - name: laravel-storefront
      urls:
        - $targetBaseUrl
      excludePaths:
        - $excludedPaths
  parameters:
    failOnError: true
    failOnWarning: false
    progressToStdout: true
jobs:
  - type: spider
    parameters:
      context: laravel-storefront
      url: $spiderUrl
      maxDuration: $SpiderMinutes
      maxDepth: 5
      maxChildren: 20
      postForm: false
      processForm: false
      logoutAvoidance: true
  - type: passiveScan-wait
    parameters:
      maxDuration: 5
  - type: report
    parameters:
      template: traditional-html
      reportDir: '$reportDirectory'
      reportFile: zap-baseline.html
      reportTitle: Laravel Storefront - ZAP Baseline
      reportDescription: Local passive baseline scan
      displayReport: false
  - type: report
    parameters:
      template: traditional-json
      reportDir: '$reportDirectory'
      reportFile: zap-baseline.json
      reportTitle: Laravel Storefront - ZAP Baseline
      reportDescription: Local passive baseline scan
      displayReport: false
"@

try {
    Set-Content -LiteralPath $planPath -Value $plan -Encoding UTF8
}
catch {
    throw "Could not create the temporary ZAP Automation Framework plan: $($_.Exception.Message)"
}

Write-Output "Running ZAP passive Baseline against $TargetUrl using $zapExecutable"
Write-Output "Reports will be saved under $reportsPath"
$locationPushed = $false
try {
    Push-Location (Split-Path -Parent $zapExecutable)
    $locationPushed = $true
    $ErrorActionPreference = 'Continue'
    & $zapExecutable -cmd -autorun $planPath
    $scanExitCode = $LASTEXITCODE
    $ErrorActionPreference = 'Stop'
}
finally {
    if ($locationPushed) {
        Pop-Location
    }
    if (Test-Path -LiteralPath $planPath) {
        Remove-Item -LiteralPath $planPath -Force
    }
}

if (-not (Test-Path -LiteralPath $htmlReport) -or -not (Test-Path -LiteralPath $jsonReport)) {
    throw 'ZAP did not generate both expected reports. Confirm that the Automation Framework and Report Generation add-ons are available.'
}

if ($scanExitCode -ne 0) {
    if ($scanExitCode -eq 2) {
        Write-Warning 'ZAP Baseline completed with warnings. Review both reports; the scan is not treated as a clean result.'
    }
    else {
        Write-Error "ZAP Baseline failed with exit code $scanExitCode. Review the ZAP output and reports."
    }
    exit $scanExitCode
}

Write-Output 'ZAP Baseline completed successfully.'
