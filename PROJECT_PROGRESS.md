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
- JMeter read-only profile: 6 GET routes gồm `/home`, `/shop`, `/product/{id}`, `/cart`, `/api/products` và `/api/products/{id}`. Dữ liệu sản phẩm được seed vào SQLite tạm với 16 sản phẩm.
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

### MySQL steady-load run

The read-only profile was run against the isolated `storefront_jmeter_test` schema with 16 seeded products. It used 10 virtual users, a 15-second ramp-up, a 120-second scheduled duration, per-thread cookies, and 500 ms think time. All six endpoints returned HTTP 200, and the run recorded no failed samples.

| Virtual users | Scheduled duration | Samples | Errors | Avg response | p95 response | Max response | Throughput |
| ------------: | -----------------: | ------: | -----: | -----------: | -----------: | -----------: | ---------: |
|            10 |             120 s |     784 |      0 |       938 ms |     1,135 ms |     1,328 ms |  6.5 req/s |

The p95 is the nearest-rank percentile calculated from the JTL samples. After the run, the test schema still contained 16 products and zero carts, cart items, or orders.

JMeter result file:

- `tests/jmeter/results-20261017-mysql-steady-10vu-120s.jtl`

The scheduler flag must be a literal `true` in the JMX; JMeter did not evaluate the prior `__P(...)` expression in this boolean field, so the old timed profile kept running when configured with infinite loops. The duration remains configurable and defaults to 120 seconds. Existing finite-loop smoke profiles still stop when their loop count is reached.

Reproduce the steady run with the Laravel server connected to `storefront_jmeter_test`:

```powershell
& 'C:\Users\admin\AppData\Local\Programs\JMeter\bin\jmeter.bat' -n -t '.\tests\jmeter\LaravelStorefrontAPI.jmx' -Jhost=localhost -Jport=8000 -Jthreads=10 -Jramp=15 -Jloops=-1 -Jduration=120 -Jthink_time=500 -j 'D:\jmeter-run\jmeter-steady-mysql.log' -l '.\tests\jmeter\results-20261017-mysql-steady-10vu-120s.jtl'
```

### JMeter authenticated checkout load

A separate write-enabled plan covers login, add-to-cart, checkout-page validation, COD order creation, and logout. Each virtual user has a dedicated `load-test-{thread number}@example.test` account and its own JMeter cookie jar. The plan checks the login/logout state, confirms the cart made it to checkout, and asserts the order confirmation. Checkout runs write orders to the database, so only run this plan against the isolated JMeter schema.

The fixture seeder is guarded: it only runs in `local` or `testing` and when the active database name contains `jmeter`. It creates or updates 100 reserved test accounts by default and removes their carts before a run; it does not delete order history. After a timed run, rerun the seeder to clear any carts left partway through a checkout iteration.

The 10-user steady-load run used a 15-second ramp-up and 500 ms think time for 120 seconds. It produced 440 top-level sampler results and 71 completed COD orders, with zero failed samples. Response-time p95 is the nearest-rank percentile. The complete login-to-logout journey time is derived from JTL timestamps; endpoint counts differ because the timed run can stop a user between requests.

| Virtual users | Duration | Samples | Completed orders | Errors | Avg response | p95 response | Max response | Throughput |
| ------------: | -------: | ------: | ---------------: | -----: | -----------: | -----------: | -----------: | ---------: |
|            10 |   120 s |     440 |               71 |      0 |     2,075 ms |     3,014 ms |     3,477 ms |  3.6 req/s |

`POST /checkout` itself averaged 2,483 ms (p95 2,957 ms; maximum 3,060 ms). The completed-order rate was approximately 0.59 orders/second. In the separate 10-user one-loop run, the full login-to-logout journey averaged 12.2 seconds (p95 12.9 seconds). After the steady run, all 71 new orders and order-item snapshots were present. The fixture seeder was rerun afterward to clear partial carts; order history remains in the isolated test database.

JMeter checkout files:

- `tests/jmeter/LaravelStorefrontCheckout.jmx`
- `tests/jmeter/results-20261017-checkout-10vu-1loop.jtl` (10-user, one-transaction-per-user validation)
- `tests/jmeter/results-20261017-mysql-checkout-steady-10vu-120s.jtl`
- `database/seeders/JMeterCheckoutSeeder.php`

Prepare the dedicated test accounts (run this before each checkout test and once afterward to clear remaining carts):

```powershell
$env:APP_ENV = 'local'
$env:DB_DATABASE = 'storefront_jmeter_test'
$env:JMETER_USER_COUNT = '100'
$env:JMETER_PRODUCT_ID = '1'
$env:JMETER_PASSWORD = 'Jmeter-Checkout-Only-2026!'
php artisan db:seed --class=JMeterCheckoutSeeder --no-interaction
```

Run the web server with `APP_ENV=local` and `DB_DATABASE=storefront_jmeter_test`, then start the steady checkout profile:

```powershell
& 'C:\Users\admin\AppData\Local\Programs\JMeter\bin\jmeter.bat' -n -t '.\tests\jmeter\LaravelStorefrontCheckout.jmx' -Jhost=localhost -Jport=8000 -Jproduct_id=1 -Jthreads=10 -Jramp=15 -Jloops=-1 -Jduration=120 -Jthink_time=500 -j 'D:\jmeter-run\jmeter-checkout-steady.log' -l 'D:\jmeter-run\results-checkout-steady.jtl'
```

## Giới hạn và việc còn lại

- Các lần chạy dùng PHP development server trên máy local; SQLite và MySQL là hai profile riêng, không phải benchmark production hoặc cam kết SLA/capacity.
- Read-only load chỉ dùng GET; checkout load tạo order COD trong database test và không thực hiện thanh toán thật.
- Tải checkout dài hơn có thể để lại đơn hàng test tích lũy; seeder chỉ dọn giỏ của các tài khoản JMeter, không xóa order history.
- Báo cáo đánh giá tổng hợp cuối cùng vẫn được hoãn theo yêu cầu.
