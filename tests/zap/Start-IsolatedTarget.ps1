param(
    [switch]$PrepareOnly
)

$ErrorActionPreference = 'Stop'
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$databaseDirectory = Join-Path $repoRoot 'storage\framework\testing'
$databasePath = Join-Path $databaseDirectory 'zap.sqlite'
$expectedDatabasePath = [System.IO.Path]::GetFullPath($databasePath)
$configCachePath = Join-Path $repoRoot 'bootstrap\cache\config.php'

if (Test-Path -LiteralPath $configCachePath) {
    throw 'Laravel config is cached. Run "php artisan config:clear" from the repository root, then retry.'
}

New-Item -ItemType Directory -Path $databaseDirectory -Force | Out-Null
if (-not (Test-Path -LiteralPath $databasePath)) {
    New-Item -ItemType File -Path $databasePath | Out-Null
}

$env:APP_ENV = 'local'
$env:APP_DEBUG = 'false'
$env:APP_URL = 'http://127.0.0.1:8017'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = $expectedDatabasePath
$env:SESSION_DRIVER = 'file'
$env:CACHE_STORE = 'file'
$env:QUEUE_CONNECTION = 'sync'
$env:MAIL_MAILER = 'log'

Push-Location $repoRoot
try {
    & php artisan migrate:fresh --seed --force --no-interaction
    if ($LASTEXITCODE -ne 0) {
        throw "Could not prepare the isolated ZAP SQLite database (exit code $LASTEXITCODE)."
    }

    if ($PrepareOnly) {
        Write-Output "Isolated ZAP target prepared: $expectedDatabasePath"
        return
    }

    Write-Output 'Serving the isolated ZAP target at http://127.0.0.1:8017. Press Ctrl+C to stop.'
    & php artisan serve --host=127.0.0.1 --port=8017
    if ($LASTEXITCODE -ne 0) {
        throw "Laravel development server exited with code $LASTEXITCODE."
    }
}
finally {
    Pop-Location
}
