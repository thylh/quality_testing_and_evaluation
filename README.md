# Cửa hàng trực tuyến Laravel

Ứng dụng cửa hàng trực tuyến được xây dựng bằng Laravel, gồm danh mục sản phẩm, tài khoản người dùng, giỏ hàng, giao diện lập trình ứng dụng sản phẩm và quy trình đặt hàng thanh toán khi nhận hàng.

## Chức năng chính

- Xem danh sách và thông tin chi tiết sản phẩm.
- Đăng ký, đăng nhập và đăng xuất.
- Quản lý giỏ hàng của khách và giỏ hàng gắn với tài khoản.
- Đặt hàng bằng phương thức thanh toán khi nhận hàng; lưu thông tin sản phẩm tại thời điểm đặt.
- Truy xuất danh sách và chi tiết sản phẩm qua giao diện lập trình ứng dụng.

## Yêu cầu môi trường

- PHP theo phiên bản được khai báo trong `composer.json`.
- Composer.
- Cơ sở dữ liệu theo cấu hình của Laravel.
- Node.js và npm nếu cần biên dịch tài nguyên giao diện.

## Cài đặt và chạy

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Nếu cần biên dịch tài nguyên giao diện, cài các gói npm và chạy máy chủ phát triển giao diện:

```powershell
npm install
npm run dev
```

Không dùng cơ sở dữ liệu sản xuất cho kiểm thử tự động, kiểm thử tải hoặc kiểm thử ZAP. Xem các tài liệu kế hoạch, tiến độ và hướng dẫn ZAP để biết phạm vi cũng như môi trường thử nghiệm riêng.

## Kiểm thử

- Kiểm thử phía máy chủ: `php artisan test`.
- Kịch bản Apache JMeter nằm trong `tests/jmeter/`.
- Tập lệnh OWASP ZAP Baseline và hướng dẫn nằm trong `tests/zap/`.
- Kết quả kiểm thử tải và báo cáo bảo mật được lưu theo cấu hình dự án; không xem các phép đo cục bộ là cam kết về năng lực sản xuất.

## Tài liệu dự án

- [Kế hoạch hoàn thiện và kiểm định](PROJECT_PLAN.md)
- [Tiến độ và kết quả kiểm thử](PROJECT_PROGRESS.md)
- [Hướng dẫn kiểm thử OWASP ZAP](tests/zap/README.md)
