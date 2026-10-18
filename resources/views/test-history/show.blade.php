@extends('layouts.app')

@section('title', 'Chi tiết kết quả kiểm thử')

@section('content')
    <main class="test-history test-history-detail">
        <a class="test-history-back" href="{{ route('test-history.index') }}">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Quay lại lịch sử
        </a>

        <section class="test-history-heading">
            <div>
                <p class="test-history-eyebrow">{{ $runDetails['tool'] }} · {{ $runDetails['date'] }}</p>
                <h1>{{ $runDetails['scenario'] }}</h1>
                <p class="test-history-intro">{{ $runDetails['summary'] }}</p>
            </div>
            @if ($runDetails['artifact_url'])
                <a class="test-history-report-link" href="{{ $runDetails['artifact_url'] }}">
                    Mở tệp kết quả
                    <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
            @endif
        </section>

        <section class="test-history-notice" aria-label="Ghi chú về dữ liệu lịch sử">
            <i class="fas fa-circle-info" aria-hidden="true"></i>
            <p>{{ $runDetails['note'] }}</p>
        </section>

        @if ($runDetails['metrics'])
            <section class="test-detail-metrics" aria-label="Chỉ số lần chạy">
                @foreach ($runDetails['metrics'] as $metric)
                    <article>
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                    </article>
                @endforeach
            </section>
        @endif

        @if (isset($runDetails['rows']) && $runDetails['rows'])
            <section class="test-detail-section">
                <h2>{{ in_array($runDetails['tool'], ['PHPUnit', 'Selenium'], true) ? 'Chi tiết từng ca kiểm thử' : 'Chi tiết từng yêu cầu' }}</h2>
                <div class="test-detail-table-wrap">
                    <table class="test-detail-table">
                        <thead>
                            <tr>
                                @foreach ($runDetails['columns'] as $column)
                                    <th scope="col">{{ $column['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($runDetails['rows'] as $row)
                                <tr>
                                    @foreach ($runDetails['columns'] as $column)
                                        <td>{{ $row[$column['key']] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if (isset($runDetails['requests']))
            <section class="test-detail-section">
                <h2>{{ $runDetails['tool'] === 'Postman/Newman' ? 'Kết quả từng request Newman' : 'Các yêu cầu trong bộ sưu tập Postman' }}</h2>
                <div class="test-detail-request-list">
                    @foreach ($runDetails['requests'] as $request)
                        <article class="test-detail-request">
                            <div class="test-detail-request-heading">
                                <span class="test-detail-method">{{ $request['method'] }}</span>
                                <h3>{{ $request['name'] }}</h3>
                            </div>
                            <code>{{ $request['url'] }}</code>
                            @if ($request['checks'])
                                <ul>
                                    @foreach ($request['checks'] as $check)
                                        @if (is_array($check))
                                            <li>
                                                <strong>{{ $check['passed'] ? 'Đạt' : 'Thất bại' }}:</strong>
                                                {{ $check['name'] }}
                                                @if ($check['error'])
                                                    — {{ $check['error'] }}
                                                @endif
                                            </li>
                                        @else
                                            <li>{{ $check }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p>Không có xác nhận trong bộ sưu tập.</p>
                            @endif
                            @if ($request['actual'])
                                <p><strong>Kết quả HTTP:</strong> {{ $request['actual']['status_code'] ?? 'Không có phản hồi' }} · {{ $request['actual']['status'] }} · {{ $request['actual']['response_time'] ?? '—' }} ms</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (isset($runDetails['alerts']))
            <section class="test-detail-section">
                <h2>Cảnh báo bảo mật chi tiết</h2>
                @forelse ($runDetails['alerts'] as $alert)
                    <article class="test-zap-alert">
                        <div class="test-zap-alert-heading">
                            <div>
                                <span class="test-zap-severity test-zap-severity--{{ \Illuminate\Support\Str::slug($alert['severity']) }}">
                                    {{ $alert['severity'] }}
                                </span>
                                <h3>{{ $alert['name'] }}</h3>
                            </div>
                            <span>Độ tin cậy ZAP: {{ $alert['confidence'] }}/3</span>
                        </div>
                        <p>{{ $alert['description'] }}</p>
                        @if ($alert['solution'])
                            <p><strong>Khuyến nghị:</strong> {{ $alert['solution'] }}</p>
                        @endif
                        @foreach ($alert['instances'] as $instance)
                            <div class="test-zap-instance">
                                <div><strong>{{ $instance['method'] }}</strong> <code>{{ $instance['url'] }}</code></div>
                                @if ($instance['evidence'])
                                    <pre>{{ $instance['evidence'] }}</pre>
                                @endif
                                @if ($instance['detail'])
                                    <p>{{ $instance['detail'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </article>
                @empty
                    <p class="test-history-empty">Báo cáo không có cảnh báo nào.</p>
                @endforelse
            </section>
        @endif

        @if (!isset($runDetails['requests']) && !isset($runDetails['alerts']) && !$runDetails['rows'])
            <section class="test-detail-section test-detail-not-recorded">
                <h2>Chưa có chi tiết lần chạy</h2>
                <p>{{ $runDetails['note'] }}</p>
            </section>
        @endif
    </main>
@endsection
