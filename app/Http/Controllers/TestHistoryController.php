<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestHistoryController extends Controller
{
    public function index(): View
    {
        $phpunitRun = $this->testReportDetails(
            'PHPUnit',
            'Kiểm thử tự động phía máy chủ',
            'phpunit.xml',
            'php artisan test'
        );
        $seleniumRun = $this->testReportDetails(
            'Selenium',
            'Kiểm thử giao diện trên trình duyệt',
            'selenium.xml',
            'npm run test:selenium'
        );
        $postmanRun = $this->postmanDetails();

        $zapReportPath = base_path('tests/zap/reports/zap-baseline.json');
        $zapReport = is_file($zapReportPath)
            ? json_decode(file_get_contents($zapReportPath), true, 512, JSON_THROW_ON_ERROR)
            : null;
        $zapAlerts = collect(data_get($zapReport, 'site.0.alerts', []));
        $zapGeneratedAt = data_get($zapReport, 'created');

        $runs = [
            [
                'id' => 'phpunit',
                'tool' => 'PHPUnit',
                'scenario' => $phpunitRun['scenario'],
                'date' => $phpunitRun['date'],
                'status' => $phpunitRun['status'],
                'status_style' => $phpunitRun['status_style'],
                'summary' => $phpunitRun['summary'],
                'details' => $phpunitRun['note'],
                'artifact' => null,
            ],
            [
                'id' => 'selenium',
                'tool' => 'Selenium',
                'scenario' => $seleniumRun['scenario'],
                'date' => $seleniumRun['date'],
                'status' => $seleniumRun['status'],
                'status_style' => $seleniumRun['status_style'],
                'summary' => $seleniumRun['summary'],
                'details' => $seleniumRun['note'],
                'artifact' => null,
            ],
            [
                'id' => 'postman',
                'tool' => 'Postman',
                'scenario' => $postmanRun['scenario'],
                'date' => $postmanRun['date'],
                'status' => $postmanRun['status'],
                'status_style' => $postmanRun['status_style'],
                'summary' => $postmanRun['summary'],
                'details' => $postmanRun['note'],
                'artifact' => null,
            ],
            [
                'id' => 'jmeter-readonly',
                'tool' => 'JMeter',
                'scenario' => 'Tải ổn định, chỉ đọc, MySQL, 10 người dùng ảo',
                'date' => '17/10/2026',
                'status' => 'Đạt',
                'status_style' => 'success',
                'summary' => '784 mẫu, 0 lỗi; trung bình 938 ms, p95 1.135 ms',
                'details' => 'Chạy 120 giây trên máy chủ PHP cục bộ; không phải phép đo năng lực sản xuất.',
                'artifact' => 'jmeter-readonly',
            ],
            [
                'id' => 'jmeter-checkout',
                'tool' => 'JMeter',
                'scenario' => 'Tải ổn định, đặt hàng COD, MySQL, 10 người dùng ảo',
                'date' => '17/10/2026',
                'status' => 'Đạt',
                'status_style' => 'success',
                'summary' => '71 đơn hoàn tất, 0 lỗi; p95 3.014 ms',
                'details' => 'Kịch bản ghi dữ liệu, chỉ chạy trên cơ sở dữ liệu kiểm thử riêng.',
                'artifact' => 'jmeter-checkout',
            ],
            [
                'id' => 'zap',
                'tool' => 'OWASP ZAP',
                'scenario' => 'Baseline thụ động trên mục tiêu cục bộ cô lập',
                'date' => $zapGeneratedAt
                    ? Carbon::parse($zapGeneratedAt)->setTimezone(config('app.timezone'))->format('d/m/Y H:i')
                        .' ('.config('app.timezone').')'
                    : '18/10/2026',
                'status' => $zapReport ? 'Có cảnh báo' : 'Chưa có báo cáo',
                'status_style' => $zapReport ? 'warning' : 'muted',
                'summary' => $zapReport
                    ? $this->formatZapSummary($zapAlerts)
                    : 'Không tìm thấy báo cáo JSON của lần quét.',
                'details' => $zapReport
                    ? '73 địa chỉ được thu thập; còn cảnh báo thấp về cookie XSRF-TOKEN.'
                    : 'Chạy lại Baseline để tạo báo cáo.',
                'artifact' => $zapReport ? 'zap-html' : null,
            ],
        ];

        foreach ($runs as &$run) {
            $run['artifact_available'] = $run['artifact'] !== null
                && is_file($this->artifactPath($run['artifact']));
            $run['detail_url'] = route('test-history.show', $run['id']);
        }
        unset($run);

        return view('test-history.index', compact('runs'));
    }

    public function show(string $run): View
    {
        $runDetails = match ($run) {
            'phpunit' => $this->testReportDetails(
                'PHPUnit',
                'Kiểm thử tự động phía máy chủ',
                'phpunit.xml',
                'php artisan test'
            ),
            'selenium' => $this->testReportDetails(
                'Selenium',
                'Kiểm thử giao diện trên trình duyệt',
                'selenium.xml',
                'npm run test:selenium'
            ),
            'postman' => $this->postmanDetails(),
            'jmeter-readonly' => $this->jmeterDetails(
                'Tải ổn định chỉ đọc trên MySQL',
                'results-20261017-mysql-steady-10vu-120s.jtl',
                'Kịch bản chạy 120 giây với 10 người dùng ảo và 500 ms thời gian nghỉ. Các dòng trong bảng được tính trực tiếp từ từng mẫu JTL.'
            ),
            'jmeter-checkout' => $this->jmeterDetails(
                'Tải ổn định đặt hàng COD trên MySQL',
                'results-20261017-mysql-checkout-steady-10vu-120s.jtl',
                'Kịch bản tạo dữ liệu trên cơ sở dữ liệu JMeter riêng. JTL có các mẫu bộ lấy mẫu cha và con; không cộng chúng thành số giao dịch nghiệp vụ.'
            ),
            'zap' => $this->zapDetails(),
            default => abort(404),
        };

        return view('test-history.show', compact('runDetails'));
    }

    public function artifact(string $artifact): BinaryFileResponse|Response
    {
        $path = $this->artifactPath($artifact);
        abort_unless(is_file($path), 404);

        if ($artifact === 'zap-html') {
            return response()->file($path, [
                'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; base-uri 'none'; form-action 'none'; frame-ancestors 'none'",
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
            ]);
        }

        return response()->download($path);
    }

    private function testReportDetails(string $tool, string $scenario, string $filename, string $command): array
    {
        $path = storage_path('app/test-results/'.$filename);
        clearstatcache(true, $path);
        if (! is_file($path) || filesize($path) === 0) {
            return [
                'tool' => $tool,
                'scenario' => $scenario,
                'date' => '—',
                'summary' => 'Chưa có báo cáo kết quả theo từng ca kiểm thử.',
                'note' => "Chạy lệnh `{$command}` để tạo báo cáo chi tiết cho lần chạy tiếp theo.",
                'metrics' => [],
                'rows' => [],
                'columns' => [],
                'status' => 'Chưa có báo cáo',
                'status_style' => 'muted',
                'artifact_url' => null,
            ];
        }

        $report = simplexml_load_file($path, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        if ($report === false) {
            throw new \UnexpectedValueException("Invalid {$tool} JUnit report: {$filename}");
        }

        $cases = $report->xpath('//testcase');
        if ($cases === false || $cases === []) {
            throw new \UnexpectedValueException("No test cases found in {$tool} JUnit report: {$filename}");
        }

        $rows = collect($cases)->map(function (\SimpleXMLElement $case): array {
            $attributes = $case->attributes();
            $failure = $case->failure ?? $case->error ?? $case->skipped ?? null;
            $result = match (true) {
                isset($case->failure) => 'Thất bại',
                isset($case->error) => 'Lỗi',
                isset($case->skipped) => 'Bỏ qua',
                default => 'Đạt',
            };
            $details = $failure === null
                ? 'Không có lỗi.'
                : trim((string) $failure);
            if ($failure !== null && strlen($details) > 800) {
                $details = substr($details, 0, 800).'…';
            }

            return [
                'suite' => (string) ($attributes['classname'] ?? '—'),
                'name' => (string) ($attributes['name'] ?? 'Ca kiểm thử không tên'),
                'result' => $result,
                'duration' => number_format((float) ($attributes['time'] ?? 0), 3, ',', '.').' giây',
                'details' => $details,
            ];
        })->all();

        $results = collect($rows);
        $passed = $results->where('result', 'Đạt')->count();
        $failed = $results->whereIn('result', ['Thất bại', 'Lỗi'])->count();
        $skipped = $results->where('result', 'Bỏ qua')->count();
        $totalDuration = collect($cases)->sum(
            fn (\SimpleXMLElement $case): float => (float) ($case->attributes()['time'] ?? 0)
        );
        $hasFailures = $failed > 0;

        return [
            'tool' => $tool,
            'scenario' => $scenario,
            'date' => Carbon::createFromTimestamp(filemtime($path))
                ->setTimezone(config('app.timezone'))
                ->format('d/m/Y H:i').' ('.config('app.timezone').')',
            'summary' => count($rows).' ca: '.$passed.' đạt, '.$failed.' lỗi, '.$skipped.' bỏ qua',
            'note' => 'Kết quả được đọc từ báo cáo JUnit của lần chạy gần nhất. Thời lượng là thời gian thực thi từng ca; chi tiết lỗi được hiển thị nếu có.',
            'metrics' => [
                ['label' => 'Tổng số ca', 'value' => (string) count($rows)],
                ['label' => 'Đạt', 'value' => (string) $passed],
                ['label' => 'Lỗi', 'value' => (string) $failed],
                ['label' => 'Bỏ qua', 'value' => (string) $skipped],
                ['label' => 'Tổng thời lượng ca', 'value' => number_format($totalDuration, 2, ',', '.').' giây'],
            ],
            'rows' => $rows,
            'columns' => [
                ['key' => 'suite', 'label' => 'Bộ kiểm thử'],
                ['key' => 'name', 'label' => 'Ca kiểm thử'],
                ['key' => 'result', 'label' => 'Kết quả'],
                ['key' => 'duration', 'label' => 'Thời lượng'],
                ['key' => 'details', 'label' => 'Chi tiết'],
            ],
            'status' => $hasFailures ? 'Có lỗi' : 'Đạt',
            'status_style' => $hasFailures ? 'warning' : 'success',
            'artifact_url' => null,
        ];
    }

    private function postmanDetails(): array
    {
        $path = base_path('tests/postman/laravel_storefront_api.postman_collection.json');
        $collection = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $reportPath = storage_path('app/test-results/postman.json');
        clearstatcache(true, $reportPath);
        if (is_file($reportPath) && filesize($reportPath) > 0) {
            $report = json_decode(file_get_contents($reportPath), true, 512, JSON_THROW_ON_ERROR);
            $stats = $report['stats'] ?? [];
            $requests = collect($report['executions'] ?? [])->map(fn (array $execution): array => [
                'name' => $execution['name'],
                'method' => $execution['method'],
                'url' => $execution['url'],
                'checks' => collect($execution['assertions'] ?? [])->map(fn (array $assertion): array => [
                    'name' => $assertion['name'],
                    'passed' => $assertion['passed'],
                    'error' => $assertion['error'],
                ])->all(),
                'actual' => [
                    'status' => $execution['status'],
                    'status_code' => $execution['statusCode'],
                    'response_time' => $execution['responseTime'],
                ],
            ])->all();
            $assertions = data_get($stats, 'assertions.total', 0);
            $failed = data_get($stats, 'assertions.failed', 0) + data_get($stats, 'requests.failed', 0);

            return [
                'tool' => 'Postman/Newman',
                'scenario' => 'Kiểm thử API cửa hàng trực tuyến',
                'date' => isset($report['generatedAt'])
                    ? Carbon::parse($report['generatedAt'])->setTimezone(config('app.timezone'))->format('d/m/Y H:i').' ('.config('app.timezone').')'
                    : '—',
                'summary' => data_get($stats, 'requests.total', count($requests)).' yêu cầu, '.$assertions.' xác nhận, '.$failed.' lỗi',
                'note' => 'Kết quả được đọc từ báo cáo Newman gần nhất. Môi trường kiểm thử dùng cơ sở dữ liệu SQLite tạm riêng; dữ liệu nghiệp vụ bị hủy sau khi chạy.',
                'metrics' => [
                    ['label' => 'Yêu cầu', 'value' => (string) data_get($stats, 'requests.total', count($requests))],
                    ['label' => 'Xác nhận', 'value' => (string) $assertions],
                    ['label' => 'Xác nhận thất bại', 'value' => (string) data_get($stats, 'assertions.failed', 0)],
                    ['label' => 'Thời lượng', 'value' => number_format(($report['duration'] ?? 0) / 1000, 2, ',', '.').' giây'],
                ],
                'requests' => $requests,
                'rows' => [],
                'columns' => [],
                'status' => $failed === 0 ? 'Đạt' : 'Có lỗi',
                'status_style' => $failed === 0 ? 'success' : 'warning',
                'artifact_url' => null,
            ];
        }

        $requests = collect($collection['item'] ?? [])->map(function (array $item): array {
            $scripts = collect($item['event'] ?? [])
                ->where('listen', 'test')
                ->flatMap(fn (array $event): array => $event['script']['exec'] ?? [])
                ->implode("\n");
            preg_match_all('/pm\.test\("([^"]+)"/', $scripts, $matches);

            return [
                'name' => $item['name'] ?? 'Yêu cầu không tên',
                'method' => data_get($item, 'request.method', '—'),
                'url' => data_get($item, 'request.url.raw', '—'),
                'checks' => $matches[1] ?? [],
                'actual' => null,
            ];
        });

        return [
            'tool' => 'Postman',
            'scenario' => 'Bộ sưu tập kiểm thử API cửa hàng trực tuyến',
            'date' => '—',
            'summary' => 'Chưa chạy Newman',
            'note' => 'Danh sách dưới đây là các request và xác nhận đã khai báo, chưa phải kết quả thực thi. Chạy `npm run test:postman` để tạo báo cáo thật.',
            'metrics' => [],
            'requests' => $requests,
            'rows' => [],
            'columns' => [],
            'status' => 'Chưa chạy',
            'status_style' => 'muted',
            'artifact_url' => null,
        ];
    }

    private function jmeterDetails(string $scenario, string $filename, string $note): array
    {
        $path = base_path('tests/jmeter/'.$filename);
        abort_unless(is_file($path), 404);

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Could not open JMeter result file: {$filename}");
        }

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers) || ! in_array('label', $headers, true)) {
                throw new \UnexpectedValueException("Invalid JMeter result file: {$filename}");
            }

            $samples = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== count($headers)) {
                    continue;
                }

                $sample = array_combine($headers, $values);
                if ($sample === false || ! is_numeric($sample['elapsed'] ?? null)) {
                    continue;
                }
                $samples[] = $sample;
            }
        } finally {
            fclose($handle);
        }

        abort_if($samples === [], 404);

        $groups = collect($samples)->groupBy('label')->map(function ($group, string $label): array {
            $times = $group->pluck('elapsed')->map(fn ($value): int => (int) $value)->sort()->values();
            $errors = $group->filter(fn (array $sample): bool => ($sample['success'] ?? '') !== 'true')->count();
            $codes = $group->countBy(fn (array $sample): string => $sample['responseCode'] ?: '—');

            return [
                'label' => $label,
                'samples' => $group->count(),
                'errors' => $errors,
                'average' => (int) round($times->avg()),
                'p95' => $times->get(max(0, (int) ceil($times->count() * 0.95) - 1)),
                'maximum' => $times->last(),
                'responses' => $codes->map(fn (int $count, string $code): string => "{$code}: {$count}")->implode(', '),
            ];
        })->values()->all();

        $elapsedTimes = collect($samples)->pluck('elapsed')->map(fn ($value): int => (int) $value)->sort()->values();
        $timestamps = collect($samples)->pluck('timeStamp')->filter(fn ($value): bool => is_numeric($value))->map(fn ($value): int => (int) $value);
        $durationSeconds = $timestamps->isEmpty()
            ? 0
            : max(1, (int) ceil(($timestamps->max() - $timestamps->min()) / 1000));
        $failures = collect($samples)->filter(fn (array $sample): bool => ($sample['success'] ?? '') !== 'true')->count();

        return [
            'tool' => 'Apache JMeter',
            'scenario' => $scenario,
            'date' => $timestamps->isEmpty()
                ? '17/10/2026'
                : Carbon::createFromTimestampMs($timestamps->max())->setTimezone(config('app.timezone'))->format('d/m/Y H:i').' ('.config('app.timezone').')',
            'summary' => count($samples).' mẫu JTL, '.$failures.' lỗi',
            'note' => $note,
            'metrics' => [
                ['label' => 'Số mẫu JTL', 'value' => (string) count($samples)],
                ['label' => 'Lỗi', 'value' => (string) $failures],
                ['label' => 'Phản hồi trung bình', 'value' => number_format((int) round($elapsedTimes->avg()), 0, ',', '.').' ms'],
                ['label' => 'Phân vị 95', 'value' => number_format((int) $elapsedTimes->get(max(0, (int) ceil($elapsedTimes->count() * 0.95) - 1)), 0, ',', '.').' ms'],
                ['label' => 'Phản hồi tối đa', 'value' => number_format((int) $elapsedTimes->last(), 0, ',', '.').' ms'],
                ['label' => 'Thông lượng ước tính', 'value' => number_format(count($samples) / $durationSeconds, 2, ',', '.').' mẫu/giây'],
            ],
            'rows' => $groups,
            'columns' => [
                ['key' => 'label', 'label' => 'Yêu cầu'],
                ['key' => 'samples', 'label' => 'Mẫu'],
                ['key' => 'errors', 'label' => 'Lỗi'],
                ['key' => 'average', 'label' => 'Trung bình (ms)'],
                ['key' => 'p95', 'label' => 'p95 (ms)'],
                ['key' => 'maximum', 'label' => 'Tối đa (ms)'],
                ['key' => 'responses', 'label' => 'Mã phản hồi'],
            ],
            'artifact_url' => route('test-history.artifact', $filename === 'results-20261017-mysql-steady-10vu-120s.jtl' ? 'jmeter-readonly' : 'jmeter-checkout'),
        ];
    }

    private function zapDetails(): array
    {
        $path = base_path('tests/zap/reports/zap-baseline.json');
        abort_unless(is_file($path), 404);
        $report = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $alerts = collect(data_get($report, 'site.0.alerts', []))->map(function (array $alert): array {
            $instances = collect($alert['instances'] ?? [])->map(fn (array $instance): array => [
                'url' => $instance['uri'] ?? '—',
                'method' => $instance['method'] ?? '—',
                'evidence' => $instance['evidence'] ?? '',
                'detail' => trim(strip_tags($instance['otherinfo'] ?? '')),
            ])->all();

            return [
                'name' => $alert['name'] ?? 'Cảnh báo không tên',
                'severity' => $this->zapSeverity($alert['riskcode'] ?? '0'),
                'confidence' => $alert['confidence'] ?? '—',
                'description' => trim(strip_tags($alert['desc'] ?? '')),
                'solution' => trim(strip_tags($alert['solution'] ?? '')),
                'instances' => $instances,
            ];
        })->all();

        $instanceCount = collect($alerts)->sum(fn (array $alert): int => count($alert['instances']));
        $generatedAt = data_get($report, 'created');

        return [
            'tool' => 'OWASP ZAP',
            'scenario' => 'Baseline thụ động trên mục tiêu cục bộ cô lập',
            'date' => $generatedAt
                ? Carbon::parse($generatedAt)->setTimezone(config('app.timezone'))->format('d/m/Y H:i').' ('.config('app.timezone').')'
                : '—',
            'summary' => count($alerts).' loại cảnh báo, '.$instanceCount.' vị trí',
            'note' => 'Danh sách dưới đây được đọc trực tiếp từ JSON Baseline. Đây là quét thụ động; cảnh báo cần được đánh giá theo ngữ cảnh.',
            'metrics' => [
                ['label' => 'Địa chỉ trong báo cáo', 'value' => (string) data_get($report, 'site.0.@name', '—')],
                ['label' => 'Cảnh báo cao', 'value' => (string) collect($alerts)->where('severity', 'Cao')->count()],
                ['label' => 'Cảnh báo trung bình', 'value' => (string) collect($alerts)->where('severity', 'Trung bình')->count()],
                ['label' => 'Cảnh báo thấp', 'value' => (string) collect($alerts)->where('severity', 'Thấp')->count()],
                ['label' => 'Cảnh báo thông tin', 'value' => (string) collect($alerts)->where('severity', 'Thông tin')->count()],
                ['label' => 'Vị trí cảnh báo', 'value' => (string) $instanceCount],
            ],
            'alerts' => $alerts,
            'rows' => [],
            'columns' => [],
            'artifact_url' => route('test-history.artifact', 'zap-html'),
        ];
    }

    private function zapSeverity(string $riskCode): string
    {
        return match ($riskCode) {
            '3' => 'Cao',
            '2' => 'Trung bình',
            '1' => 'Thấp',
            default => 'Thông tin',
        };
    }

    private function formatZapSummary(\Illuminate\Support\Collection $alerts): string
    {
        $medium = $alerts->where('riskcode', '2')->count();
        $low = $alerts->where('riskcode', '1')->count();
        $informational = $alerts->where('riskcode', '0')->count();

        return "{$medium} Trung bình, {$low} Thấp, {$informational} Thông tin";
    }

    private function artifactPath(string $artifact): string
    {
        $artifacts = [
            'jmeter-readonly' => base_path('tests/jmeter/results-20261017-mysql-steady-10vu-120s.jtl'),
            'jmeter-checkout' => base_path('tests/jmeter/results-20261017-mysql-checkout-steady-10vu-120s.jtl'),
            'zap-html' => base_path('tests/zap/reports/zap-baseline.html'),
        ];

        abort_unless(array_key_exists($artifact, $artifacts), 404);

        return $artifacts[$artifact];
    }
}
