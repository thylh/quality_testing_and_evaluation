# Kiểm thử bảo mật thụ động bằng OWASP ZAP

Tích hợp ZAP hiện chỉ chạy Baseline thụ động. Công cụ thu thập địa chỉ cục bộ và phân tích thụ động; không bật bộ quét chủ động và không gửi biểu mẫu đặt hàng. ZAP Desktop được chạy trực tiếp trên Windows; không cần Docker.

## Yêu cầu

- Windows PowerShell.
- OWASP ZAP phiên bản 2.17 trở lên cho Windows, có Bộ khung tự động hóa và tiện ích tạo báo cáo.
- PHP và các gói phụ thuộc Composer đã được cài đặt.
- Môi trường chạy Java 17 trở lên có trong `PATH` nếu dùng gói đa nền tảng.

Nếu ZAP được cài ngoài các thư mục thông dụng, đặt biến `ZAP_HOME` trỏ đến thư mục cài đặt hoặc truyền `-ZapPath` kèm đường dẫn đầy đủ đến `zap.bat`.

Có thể giải nén gói ZIP đa nền tảng của ZAP vào `%LOCALAPPDATA%\Programs\OWASP-ZAP` và chạy mà không cần quyền quản trị. Tập lệnh quét tìm các tệp `zap.bat` có phiên bản trong thư mục này. Cũng có thể dùng bộ cài Windows chính thức nếu có quyền cài đặt.

## Khởi chạy môi trường mục tiêu cô lập

Tập lệnh tạo cơ sở dữ liệu SQLite riêng tại `storage/framework/testing/zap.sqlite`, chạy chuyển đổi lược đồ và nạp dữ liệu sản phẩm vào tệp đó, sau đó khởi chạy Laravel tại `127.0.0.1:8017`. Tiến trình này ghi đè cấu hình cơ sở dữ liệu, phiên, bộ nhớ đệm, hàng đợi và thư điện tử; không sử dụng cơ sở dữ liệu trong `.env`.

Từ thư mục gốc của dự án, chạy:

```powershell
.\tests\zap\Start-IsolatedTarget.ps1
```

Giữ cửa sổ lệnh này mở. Mở cửa sổ lệnh thứ hai và chạy Baseline:

```powershell
.\tests\zap\Invoke-BaselineScan.ps1
```

Tập lệnh chỉ chấp nhận URL gốc HTTP cổng `8017` với máy chủ `localhost`, `127.0.0.1` hoặc `::1`. Trước khi chạy ZAP, tập lệnh kiểm tra ứng dụng có phản hồi. Báo cáo được ghi vào `tests/zap/reports/zap-baseline.html` và `tests/zap/reports/zap-baseline.json`; báo cáo sinh ra và cơ sở dữ liệu SQLite riêng được bỏ qua bởi Git.

Để chỉ chuẩn bị cơ sở dữ liệu mà không khởi chạy máy chủ:

```powershell
.\tests\zap\Start-IsolatedTarget.ps1 -PrepareOnly
```

Lệnh này chỉ tạo lại lược đồ SQLite riêng cho ZAP. Không đổi đường dẫn cơ sở dữ liệu trong tập lệnh thành cơ sở dữ liệu phát triển hoặc cơ sở dữ liệu dùng chung.

## Tiêu đề bảo mật và cảnh báo đã xác minh

Phần mềm trung gian Laravel đặt Chính sách bảo mật nội dung dùng mã nonce, `X-Frame-Options: DENY` và `X-Content-Type-Options: nosniff`; đồng thời gỡ `X-Powered-By` khi có thể. Khi triển khai Apache, `public/.htaccess` đặt thêm tiêu đề chống nhúng và chống nhận diện sai kiểu nội dung cho tệp tĩnh.

Máy chủ PHP tích hợp dùng cho lần quét này phục vụ tệp tĩnh bên ngoài phần mềm trung gian Laravel. Do đó, ZAP vẫn có thể báo thiếu `X-Content-Type-Options` trên tệp tĩnh; nếu triển khai bằng máy chủ khác Apache, cần cấu hình tiêu đề tương đương tại máy chủ đó.

Cookie `XSRF-TOKEN` của cửa hàng vẫn có thể được mã phía trình duyệt đọc để hỗ trợ cơ chế chống giả mạo yêu cầu liên trang của Laravel. Cookie phiên vẫn có cờ HttpOnly. Các bảng định kiểu Google Fonts và Font Awesome bên ngoài hiện chưa có thuộc tính SRI nên vẫn là cảnh báo Baseline.

Chính sách CSP cho phép các máy chủ phân phối bảng định kiểu và phông chữ đang được sử dụng. Kiểu nội tuyến được cho phép theo mã nonce riêng của từng phản hồi; tập lệnh nội tuyến và bộ xử lý sự kiện nội tuyến không được cho phép.

## Phạm vi và giới hạn

- Chỉ chấp nhận địa chỉ gốc cục bộ cổng `8017`. Kế hoạch tự động hóa chạy bộ thu thập địa chỉ, chờ phân tích thụ động và tạo báo cáo HTML/JSON.
- Không có tác vụ quét chủ động; đây không phải quét có xác thực.
- Ứng dụng được khởi chạy với `APP_DEBUG=false` và lưu phiên/bộ nhớ đệm vào tệp.
- Bộ quét có thể tạo tệp phiên và bộ nhớ đệm cục bộ, nhưng không được tạo đơn hàng hoặc sửa đổi dữ liệu nghiệp vụ lâu dài. Cơ sở dữ liệu mục tiêu có thể loại bỏ.
- Không dùng tập lệnh này với môi trường sản xuất, môi trường kiểm thử dùng chung hoặc máy chủ không phải loopback.
- Chưa cấu hình quét chủ động hoặc quét có xác thực. Chỉ bổ sung sau khi đã xác định tài khoản thử nghiệm riêng, URL trong phạm vi và loại trừ các đường dẫn thay đổi dữ liệu như `/checkout`, `/addtocart/*`, `/logout` và thao tác cập nhật/xóa giỏ hàng.
- Cảnh báo ZAP là dấu hiệu cần xác minh, không mặc nhiên là bằng chứng có thể khai thác; cần xem xét cảnh báo sai trước khi ghi nhận lỗi.
- ZAP có thể ghi cảnh báo tích hợp trình duyệt tùy chọn khi chưa cài Firefox. Bộ thu thập truyền thống và phân tích thụ động không cần Firefox; khi chạy lại vẫn cần xem phần thông tin phân tích của báo cáo và nhật ký ZAP.
