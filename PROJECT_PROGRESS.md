# Tiến độ dự án cửa hàng trực tuyến Laravel

Cập nhật: 18/10/2026

## 1. Chức năng đã hoàn thành

- Hoàn thiện đăng ký, đăng nhập, đăng xuất, giỏ hàng khách, quyền sở hữu giỏ hàng và kiểm tra dữ liệu.
- Hoàn thiện giao diện lập trình ứng dụng sản phẩm và bộ sưu tập Postman.
- Hoàn thiện đặt hàng chỉ dành cho người dùng đã đăng nhập: thanh toán khi nhận hàng, mô phỏng thành công/thất bại, lưu bản chụp đơn hàng và giữ giỏ hàng nếu thanh toán thất bại.
- Không triển khai tìm kiếm sản phẩm, phân quyền theo vai trò hoặc cổng thanh toán thực tế.

## 2. Kết quả kiểm thử

- PHPUnit: 28 bài kiểm thử đạt trong lần chạy gần nhất.
- Selenium: 7 bài kiểm thử đạt, bao gồm luồng đặt hàng thất bại rồi thử lại thành công.
- Postman/Newman: 3 yêu cầu, 6 xác nhận, không có lỗi.
- JMeter: có kịch bản chỉ đọc và kịch bản đặt hàng có ghi dữ liệu.
- OWASP ZAP: Baseline thụ động hoàn tất trên môi trường cục bộ cô lập.

## 3. Kết quả JMeter

### 3.1. Kịch bản chỉ đọc trên MySQL

Kịch bản bao phủ sáu điểm cuối GET: `/home`, `/shop`, `/product/{id}`, `/cart`, `/api/products` và `/api/products/{id}`. Cơ sở dữ liệu kiểm thử `storefront_jmeter_test` được nạp 16 sản phẩm. Các lần chạy dùng máy chủ PHP phát triển trên máy cục bộ; đây không phải phép đo môi trường sản xuất.

| Người dùng ảo | Mẫu yêu cầu | Lỗi | Phản hồi trung bình | Phân vị 95 | Phản hồi tối đa | Thông lượng |
| ------------: | ----------: | --: | ------------------: | ---------: | --------------: | ----------: |
| 1 | 12 | 0 | 153 mili giây | 181 mili giây | 181 mili giây | 6,1 yêu cầu/giây |
| 10 | 120 | 0 | 969 mili giây | 1.398 mili giây | 1.427 mili giây | 7,3 yêu cầu/giây |
| 25 | 300 | 0 | 2.567 mili giây | 4.060 mili giây | 4.297 mili giây | 7,2 yêu cầu/giây |

Tệp kết quả:

- `tests/jmeter/results-20261017-mysql-1vu.jtl`
- `tests/jmeter/results-20261017-mysql-10vu.jtl`
- `tests/jmeter/results-20261017-mysql-25vu.jtl`

### 3.2. Tải ổn định chỉ đọc

Lần chạy dùng 10 người dùng ảo, tăng tải trong 15 giây, chạy 120 giây, thời gian nghỉ 500 mili giây giữa yêu cầu và cookie riêng theo luồng. Cả sáu điểm cuối trả HTTP 200; không có mẫu lỗi.

| Người dùng ảo | Thời lượng | Mẫu yêu cầu | Lỗi | Phản hồi trung bình | Phân vị 95 | Phản hồi tối đa | Thông lượng |
| ------------: | ---------: | ----------: | --: | ------------------: | ---------: | --------------: | ----------: |
| 10 | 120 giây | 784 | 0 | 938 mili giây | 1.135 mili giây | 1.328 mili giây | 6,5 yêu cầu/giây |

Phân vị 95 được tính theo phương pháp hạng gần nhất từ các mẫu JTL. Sau lần chạy, cơ sở dữ liệu kiểm thử còn 16 sản phẩm và không có giỏ hàng, mặt hàng giỏ hoặc đơn hàng.

Tệp kết quả: `tests/jmeter/results-20261017-mysql-steady-10vu-120s.jtl`.

Trong tệp JMX, bộ lập lịch phải dùng giá trị boolean `true` cố định; JMeter không diễn giải biểu thức `__P(...)` trước đó trong trường này. Thời lượng vẫn có thể cấu hình và mặc định là 120 giây. Các kịch bản giới hạn số vòng lặp vẫn dừng theo số vòng đã đặt.

Chạy lại từ thư mục gốc sau khi ứng dụng kết nối đến `storefront_jmeter_test`:

```powershell
& 'C:\Users\admin\AppData\Local\Programs\JMeter\bin\jmeter.bat' -n -t '.\tests\jmeter\LaravelStorefrontAPI.jmx' -Jhost=localhost -Jport=8000 -Jthreads=10 -Jramp=15 -Jloops=-1 -Jduration=120 -Jthink_time=500 -j 'D:\jmeter-run\jmeter-steady-mysql.log' -l '.\tests\jmeter\results-20261017-mysql-steady-10vu-120s.jtl'
```

### 3.3. Tải ổn định cho luồng đặt hàng

Kịch bản riêng bao phủ đăng nhập, thêm sản phẩm vào giỏ, kiểm tra trang đặt hàng, tạo đơn thanh toán khi nhận hàng và đăng xuất. Mỗi người dùng ảo có tài khoản `load-test-{số luồng}@example.test` riêng cùng vùng cookie riêng. Kịch bản kiểm tra trạng thái đăng nhập/đăng xuất, giỏ hàng và xác nhận đơn hàng.

Bộ nạp dữ liệu kiểm thử chỉ chạy trong môi trường `local` hoặc `testing` và khi tên cơ sở dữ liệu có chứa `jmeter`. Mặc định, bộ nạp tạo/cập nhật 100 tài khoản thử nghiệm dành riêng và xóa giỏ hàng của họ trước lần chạy; không xóa lịch sử đơn hàng.

Lần chạy ổn định dùng 10 người dùng ảo, tăng tải trong 15 giây, nghỉ 500 mili giây giữa yêu cầu và chạy 120 giây. Có 440 kết quả bộ lấy mẫu cấp cao nhất, 71 đơn hoàn tất và không có mẫu lỗi. Các bộ lấy mẫu có thể khác nhau vì lần chạy định thời có thể dừng giữa chừng trong một luồng.

| Người dùng ảo | Thời lượng | Mẫu yêu cầu | Đơn hoàn tất | Lỗi | Phản hồi trung bình | Phân vị 95 | Phản hồi tối đa | Thông lượng |
| ------------: | ---------: | ----------: | -----------: | --: | ------------------: | ---------: | --------------: | ----------: |
| 10 | 120 giây | 440 | 71 | 0 | 2.075 mili giây | 3.014 mili giây | 3.477 mili giây | 3,6 yêu cầu/giây |

Riêng `POST /checkout` có phản hồi trung bình 2.483 mili giây, phân vị 95 là 2.957 mili giây và tối đa 3.060 mili giây. Tốc độ hoàn tất đơn xấp xỉ 0,59 đơn/giây. Trong lần chạy riêng với một giao dịch cho mỗi người dùng, toàn bộ hành trình đăng nhập đến đăng xuất trung bình 12,2 giây, phân vị 95 là 12,9 giây.

Sau lần chạy ổn định, 71 đơn mới và các bản chụp mặt hàng đều có trong cơ sở dữ liệu. Bộ nạp dữ liệu được chạy lại để dọn các giỏ dở dang; lịch sử đơn vẫn còn trong cơ sở dữ liệu kiểm thử riêng.

Tệp và dữ liệu liên quan:

- `tests/jmeter/LaravelStorefrontCheckout.jmx`
- `tests/jmeter/results-20261017-checkout-10vu-1loop.jtl`
- `tests/jmeter/results-20261017-mysql-checkout-steady-10vu-120s.jtl`
- `database/seeders/JMeterCheckoutSeeder.php`

Chuẩn bị tài khoản thử nghiệm trước và sau mỗi lần chạy để xóa các giỏ còn lại:

```powershell
$env:APP_ENV = 'local'
$env:DB_DATABASE = 'storefront_jmeter_test'
$env:JMETER_USER_COUNT = '100'
$env:JMETER_PRODUCT_ID = '1'
$env:JMETER_PASSWORD = 'Jmeter-Checkout-Only-2026!'
php artisan db:seed --class=JMeterCheckoutSeeder --no-interaction
```

Chạy máy chủ với `APP_ENV=local` và `DB_DATABASE=storefront_jmeter_test`, sau đó chạy kịch bản:

```powershell
& 'C:\Users\admin\AppData\Local\Programs\JMeter\bin\jmeter.bat' -n -t '.\tests\jmeter\LaravelStorefrontCheckout.jmx' -Jhost=localhost -Jport=8000 -Jproduct_id=1 -Jthreads=10 -Jramp=15 -Jloops=-1 -Jduration=120 -Jthink_time=500 -j 'D:\jmeter-run\jmeter-checkout-steady.log' -l 'D:\jmeter-run\results-checkout-steady.jtl'
```

## 4. OWASP ZAP Baseline

- Đã cài ZAP 2.17.0 từ gói đa nền tảng chính thức tại `%LOCALAPPDATA%\Programs\OWASP-ZAP`; không dùng Docker và không cần quyền quản trị.
- Tập lệnh `tests/zap/Invoke-BaselineScan.ps1` tự tìm chương trình ZAP, chạy bộ khung tự động hóa, thu thập địa chỉ và phân tích thụ động, sau đó tạo báo cáo HTML/JSON.
- Lần quét ngày 18/10/2026 dùng địa chỉ `http://127.0.0.1:8017`, cơ sở dữ liệu SQLite riêng và `APP_DEBUG=false`. Bộ thu thập tìm thấy 75 địa chỉ; các yêu cầu trong phạm vi đều là GET. Không gửi biểu mẫu; các đường dẫn đăng nhập, đăng ký, đăng xuất, đặt hàng và sửa đổi giỏ hàng được loại trừ hoặc không thu thập.
- Tệp báo cáo: `tests/zap/reports/zap-baseline.html` và `tests/zap/reports/zap-baseline.json`; tệp sinh ra được bỏ qua bởi Git.
- Đã thêm phần mềm trung gian đặt tiêu đề bảo mật: CSP dùng mã nonce, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` và gỡ `X-Powered-By`. `public/.htaccess` bổ sung tiêu đề cho tệp tĩnh trên Apache. Đã loại bỏ bộ xử lý sự kiện nội tuyến của nút thêm giỏ hàng và chuyển kiểu trình bày nội tuyến liên quan sang lớp CSS.
- Đã tự lưu trữ Font Awesome 6.4.2 và phông League Spartan qua Vite; xóa các bảng định kiểu Bunny Fonts, Google Fonts và cdnjs khỏi trang. CSP được thu hẹp để chỉ cho phép tài nguyên kiểu chữ cùng phông cục bộ.
- Baseline mới nhất: **0 cảnh báo Trung bình, 1 cảnh báo Thấp, 3 cảnh báo Thông tin**. Không còn cảnh báo SRI; các cảnh báo CSP, chống nhúng trang, header MIME và `X-Powered-By` đã được xử lý.
- Cảnh báo thấp còn lại là cookie `XSRF-TOKEN` không có HttpOnly, cần cho luồng CSRF phía trình duyệt. Cookie phiên vẫn có HttpOnly. Máy chủ ZAP dùng bộ định tuyến kiểm thử đặt header bảo mật cho tệp tĩnh, tương ứng với cấu hình Apache trong `.htaccess`; cần cấu hình header tương đương nếu triển khai trên máy chủ khác Apache.
- PHPUnit đạt 28 bài kiểm thử; bộ thu thập ZAP tìm thấy 73 địa chỉ và cơ sở dữ liệu mục tiêu vẫn tách biệt. Chưa chạy quét có xác thực hoặc quét chủ động; không thay đổi máy móc thuộc tính của `XSRF-TOKEN`.
- Thông tin phân tích của ZAP có thể ghi nhận cảnh báo phụ trợ do thiếu tích hợp trình duyệt Firefox/Selenium. Bộ thu thập truyền thống và phân tích thụ động không cần Firefox; xem đây là giới hạn môi trường, không phải lỗi làm quét thất bại.

## 5. Giới hạn và việc còn lại

- Các phép đo JMeter dùng máy chủ PHP phát triển trên máy cục bộ, không phải phép đo môi trường sản xuất hoặc cam kết mức dịch vụ/năng lực.
- Kịch bản chỉ đọc chỉ gửi GET; kịch bản đặt hàng tạo đơn thanh toán khi nhận hàng trong cơ sở dữ liệu thử nghiệm, không thực hiện thanh toán thật.
- Các lần chạy đặt hàng dài có thể tích lũy đơn thử nghiệm. Bộ nạp dọn giỏ của tài khoản JMeter nhưng không xóa lịch sử đơn.
- Quét ZAP hiện chỉ là Baseline thụ động trong môi trường cục bộ; chưa có quét chủ động hoặc có xác thực.
- Báo cáo tổng hợp đánh giá chất lượng cuối cùng đang được tạm hoãn theo yêu cầu.
