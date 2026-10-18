@extends('layouts.app')

@section('title', 'Lịch sử kiểm thử')

@section('content')
    <main class="test-history">
        <section class="test-history-heading">
            <div>
                <p class="test-history-eyebrow">BẢNG ĐIỀU KHIỂN KIỂM ĐỊNH</p>
                <h1>Lịch sử kiểm thử</h1>
                <p class="test-history-intro">Tổng hợp các lần chạy đã ghi nhận. Trang này chỉ xem kết quả, không khởi chạy
                    lại công cụ.</p>
            </div>
            <div class="test-history-count">
                <strong>{{ count($runs) }}</strong>
                <span>lần chạy được ghi nhận</span>
            </div>
        </section>

        <section class="test-history-notice" aria-label="Lưu ý về dữ liệu">
            <i class="fas fa-circle-info" aria-hidden="true"></i>
            <p>Kết quả hiển thị là dữ liệu lịch sử, không phải trạng thái chạy trực tiếp. JMeter chạy trên máy chủ cục bộ;
                kịch bản đặt hàng chỉ dùng cơ sở dữ liệu kiểm thử.</p>
        </section>

        <section class="test-history-grid" aria-label="Các kết quả kiểm thử">
            @foreach ($runs as $run)
                <article class="test-run-card">
                    <div class="test-run-card-top">
                        <span class="test-run-tool">{{ $run['tool'] }}</span>
                        <span class="test-run-status test-run-status--{{ $run['status_style'] }}">
                            <span aria-hidden="true"></span>{{ $run['status'] }}
                        </span>
                    </div>
                    <h2>{{ $run['scenario'] }}</h2>
                    <p class="test-run-summary">{{ $run['summary'] }}</p>
                    <p class="test-run-details">{{ $run['details'] }}</p>
                    <div class="test-run-card-footer">
                        <span>{{ $run['date'] }}</span>
                        <a href="{{ $run['detail_url'] }}">
                            Xem chi tiết
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="test-history-footnote">
            <h2>Ghi chú phạm vi</h2>
            <p>PHPUnit, Selenium và Postman/Newman hiển thị chi tiết từ báo cáo lần chạy gần nhất. Newman dùng cơ sở dữ liệu
                thử nghiệm SQLite tạm riêng. JMeter và ZAP đọc trực tiếp tệp kết quả đã lưu trong dự án.</p>
        </section>
    </main>
@endsection