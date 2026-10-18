<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestHistoryTest extends TestCase
{
    public function test_history_page_shows_recorded_tool_results_and_artifact_links(): void
    {
        $this->get(route('test-history.index'))
            ->assertOk()
            ->assertSee('Lịch sử kiểm thử')
            ->assertSee('PHPUnit')
            ->assertSee('Selenium')
            ->assertSee('784 mẫu, 0 lỗi')
            ->assertSee('0 Trung bình, 1 Thấp, 3 Thông tin')
            ->assertSee(route('test-history.show', 'jmeter-readonly'), false)
            ->assertSee(route('test-history.show', 'zap'), false);
    }

    public function test_jmeter_artifacts_are_downloaded_from_the_allowlist(): void
    {
        $this->get(route('test-history.artifact', 'jmeter-readonly'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_jmeter_detail_shows_metrics_for_each_recorded_sampler(): void
    {
        $this->get(route('test-history.show', 'jmeter-readonly'))
            ->assertOk()
            ->assertSee('Chi tiết từng yêu cầu')
            ->assertSee('GET /home')
            ->assertSee('GET /api/products')
            ->assertSee('135');
    }

    public function test_checkout_detail_shows_order_creation_sampler(): void
    {
        $this->get(route('test-history.show', 'jmeter-checkout'))
            ->assertOk()
            ->assertSee('POST /checkout')
            ->assertSee('71');
    }

    public function test_zap_detail_shows_alerts_and_evidence(): void
    {
        $this->get(route('test-history.show', 'zap'))
            ->assertOk()
            ->assertSee('Cảnh báo bảo mật chi tiết')
            ->assertSee('Cookie No HttpOnly Flag')
            ->assertSee('XSRF-TOKEN')
            ->assertSee('http://127.0.0.1:8017/home');
    }

    public function test_postman_detail_distinguishes_collection_checks_from_saved_run_output(): void
    {
        $reportPath = storage_path('app/test-results/postman.json');
        $originalReport = is_file($reportPath) ? file_get_contents($reportPath) : null;
        file_put_contents($reportPath, json_encode([
            'generatedAt' => '2026-10-19T00:00:00+00:00',
            'duration' => 1500,
            'stats' => [
                'requests' => ['total' => 2, 'failed' => 0],
                'assertions' => ['total' => 4, 'failed' => 1],
            ],
            'executions' => [
                [
                    'name' => 'Products - List',
                    'method' => 'GET',
                    'url' => '/api/products',
                    'status' => 'OK',
                    'statusCode' => 200,
                    'responseTime' => 25,
                    'assertions' => [
                        ['name' => 'Returns a non-empty product array', 'passed' => true, 'error' => null],
                        ['name' => 'Products include public fields', 'passed' => false, 'error' => 'Missing field brand'],
                    ],
                ],
                [
                    'name' => 'Auth - Login',
                    'method' => 'POST',
                    'url' => '/api/login',
                    'status' => 'OK',
                    'statusCode' => 200,
                    'responseTime' => 30,
                    'assertions' => [
                        ['name' => 'Login returns HTTP 200', 'passed' => true, 'error' => null],
                        ['name' => 'Login returns a user and token', 'passed' => true, 'error' => null],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $this->get(route('test-history.show', 'postman'))
                ->assertOk()
                ->assertSee('2 yêu cầu, 4 xác nhận, 1 lỗi')
                ->assertSee('Kết quả từng request Newman')
                ->assertSee('Products - List')
                ->assertSee('Auth - Login')
                ->assertSee('Returns a non-empty product array')
                ->assertSee('Missing field brand')
                ->assertSee('Thất bại')
                ->assertSee('200 · OK · 25 ms');
        } finally {
            if ($originalReport === null) {
                unlink($reportPath);
            } else {
                file_put_contents($reportPath, $originalReport);
            }
        }
    }

    public function test_phpunit_and_selenium_details_show_cases_from_junit_reports(): void
    {
        $reportDirectory = storage_path('app/test-results');
        if (! is_dir($reportDirectory)) {
            mkdir($reportDirectory, 0777, true);
        }

        $reports = [
            'phpunit' => [
                'filename' => 'phpunit.xml',
                'class' => 'Tests\\Feature\\CartTest',
                'case' => 'test_cart_shows_saved_items',
            ],
            'selenium' => [
                'filename' => 'selenium.xml',
                'class' => 'tests/selenium/authentication.test.js',
                'case' => 'buyer_can_add_a_product_to_cart',
            ],
        ];
        $originalReports = [];
        foreach ($reports as $run => $report) {
            $path = $reportDirectory.'/'.$report['filename'];
            $originalReports[$path] = is_file($path) ? file_get_contents($path) : null;
        }

        try {
            foreach ($reports as $run => $report) {
                $path = $reportDirectory.'/'.$report['filename'];
                file_put_contents($path, '<?xml version="1.0"?><testsuites><testsuite name="Example"><testcase classname="'.$report['class'].'" name="'.$report['case'].'" time="0.125"/><testcase classname="'.$report['class'].'" name="test_reports_failure" time="0.250"><failure message="Assertion failed">Expected value did not match.</failure></testcase></testsuite></testsuites>');

                $this->get(route('test-history.show', $run))
                    ->assertOk()
                    ->assertSee('Chi tiết từng ca kiểm thử')
                    ->assertSee($report['case'])
                    ->assertSee('Đạt')
                    ->assertSee('Thất bại')
                    ->assertSee('Expected value did not match.')
                    ->assertSee('0,125 giây');
            }
        } finally {
            foreach ($originalReports as $path => $contents) {
                if ($contents === null) {
                    if (is_file($path)) {
                        unlink($path);
                    }
                } else {
                    file_put_contents($path, $contents);
                }
            }
        }
    }

    public function test_zap_report_is_served_with_restrictive_headers(): void
    {
        $this->get(route('test-history.artifact', 'zap-html'))
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('x-frame-options', 'DENY');
    }

    public function test_unknown_artifacts_are_not_accessible(): void
    {
        $this->get('/test-history/artifacts/../../.env')
            ->assertNotFound();
    }

    public function test_unknown_test_run_is_not_accessible(): void
    {
        $this->get('/test-history/unknown')
            ->assertNotFound();
    }
}
