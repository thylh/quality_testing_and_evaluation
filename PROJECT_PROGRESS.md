# Tiến độ dự án Laravel Storefront

Cập nhật: 2026-10-17

## Đã hoàn thành

- Hoàn thiện đăng ký, đăng nhập, đăng xuất, guest cart, cart ownership và validation.
- Hoàn thiện Product API và collection Postman.
- Triển khai checkout chỉ dành cho người dùng đã đăng nhập: COD, thanh toán mô phỏng thành công/thất bại, lưu snapshot đơn hàng và bảo toàn giỏ khi thanh toán thất bại.
- Search được xác định là ngoài phạm vi; không triển khai role hoặc cổng thanh toán thật.

## Kết quả kiểm thử

- PHPUnit: 27 tests passed, 111 assertions.
- Selenium: 7 tests passed, gồm checkout mô phỏng thất bại rồi thử lại thành công.
- Postman/Newman: 3 requests, 6 assertions, 0 failures.
- JMeter: 6 GET routes gồm `/home`, `/shop`, `/product/{id}`, `/cart`, `/api/products` và `/api/products/{id}`. Dữ liệu sản phẩm được seed vào SQLite tạm với 16 sản phẩm.
- Mỗi profile chạy 2 vòng cho mỗi virtual user; ramp-up lần lượt là 1, 5 và 10 giây.

### JMeter MySQL test run

Sau đó, cùng plan được chạy lại với schema MySQL riêng `storefront_jmeter_test`, 16 sản phẩm và cùng 6 GET routes. Ứng dụng vẫn chạy bằng PHP development server trên máy local; đây là kết quả MySQL-backed, chưa phải production benchmark.

| Virtual users | Samples | Errors | Avg response | p95 response | Max response | Throughput |
| ------------: | ------: | -----: | -----------: | -----------: | -----------: | ---------: |
|             1 |      12 |      0 |       153 ms |       181 ms |       181 ms |  6.1 req/s |
|            10 |     120 |      0 |       969 ms |     1,398 ms |     1,427 ms |  7.3 req/s |
|            25 |     300 |      0 |     2,567 ms |     4,060 ms |     4,297 ms |  7.2 req/s |

JMeter MySQL result files:

- `tests/jmeter/results-20261017-mysql-1vu.jtl`
- `tests/jmeter/results-20261017-mysql-10vu.jtl`
- `tests/jmeter/results-20261017-mysql-25vu.jtl`

### MySQL steady-load profile (prepared, not yet executed)

The JMeter plan now supports a 10-user scheduled run with a 15-second ramp-up, 120-second duration, per-thread cookies, and 500 ms think time. Preserve the short profiles above as smoke/load baselines. The steady run needs to be executed while Laravel is connected to `storefront_jmeter_test`:

```powershell
Copy-Item '.\tests\jmeter\LaravelStorefrontAPI.jmx' 'D:\jmeter-run\LaravelStorefrontAPI.jmx' -Force
& 'C:\Users\admin\AppData\Local\Programs\JMeter\bin\jmeter.bat' -n -t 'D:\jmeter-run\LaravelStorefrontAPI.jmx' -Jhost=localhost -Jport=8000 -Jthreads=10 -Jramp=15 -Jloops=-1 -Jscheduler=true -Jduration=120 -Jthink_time=500 -j 'D:\jmeter-run\jmeter-steady-mysql.log' -l 'D:\jmeter-run\results-steady-mysql.jtl'
```

| Virtual users | Samples | Errors | Avg response | p95 response | Max response | Throughput |
| ------------: | ------: | -----: | -----------: | -----------: | -----------: | ---------: |
|             1 |      12 |      0 |       119 ms |       155 ms |       155 ms |  7.7 req/s |
|            10 |     120 |      0 |       736 ms |     1,044 ms |     1,055 ms |  8.8 req/s |
|            25 |     300 |      0 |     1,975 ms |     2,715 ms |     2,755 ms |  8.7 req/s |

JMeter result files:

- `tests/jmeter/results-20261017-1vu.jtl`
- `tests/jmeter/results-20261017-10vu.jtl`
- `tests/jmeter/results-20261017-25vu.jtl`

## Giới hạn và việc còn lại

- Các lần chạy dùng PHP development server trên máy local; SQLite và MySQL là hai profile kiểm thử riêng, không phải cam kết năng lực production.
- Các profile đã lưu ngắn; steady-load profile ở trên mới chuẩn bị, chưa chạy và chưa được dùng để kết luận SLA.
- Các bài tải chỉ dùng GET, không tạo order hoặc thay đổi dữ liệu.
- Báo cáo đánh giá tổng hợp cuối cùng vẫn được hoãn theo yêu cầu.
