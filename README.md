# Cửa hàng trực tuyến Laravel

Ứng dụng cửa hàng trực tuyến được xây dựng bằng Laravel, gồm danh mục sản phẩm, tài khoản người dùng, giỏ hàng, API JSON và quy trình đặt hàng thanh toán khi nhận hàng.

## Chức năng chính

- Xem danh sách và thông tin chi tiết sản phẩm.
- Đăng ký, đăng nhập và đăng xuất.
- Quản lý giỏ hàng của khách và giỏ hàng gắn với tài khoản.
- Đặt hàng bằng phương thức thanh toán khi nhận hàng; lưu thông tin sản phẩm tại thời điểm đặt.
- API sản phẩm, xác thực bằng token truy cập cá nhân, giỏ hàng và đơn hàng.

## Yêu cầu môi trường

- PHP theo phiên bản được khai báo trong `composer.json`.
- Composer.
- Cơ sở dữ liệu theo cấu hình của Laravel.
- Node.js và npm nếu cần biên dịch tài nguyên giao diện hoặc chạy Newman/Selenium.

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

Để tạo tài nguyên giao diện dùng khi triển khai:

```powershell
npm run build
```

Không dùng cơ sở dữ liệu phát triển hoặc sản xuất cho kiểm thử tự động, kiểm thử tải hoặc kiểm thử ZAP. Newman và Selenium tạo cơ sở dữ liệu SQLite tạm riêng; xem kế hoạch và tiến độ để biết chi tiết.

## Kiểm thử

- PHPUnit: `php artisan test`; báo cáo từng ca được lưu tại `storage/app/test-results/phpunit.xml`.
- Selenium: `npm run test:selenium`; báo cáo từng kịch bản được lưu tại `storage/app/test-results/selenium.xml`.
- API/Postman-Newman: `npm run test:postman`; runner khởi chạy ứng dụng trên loopback với SQLite tạm riêng và lưu kết quả từng request/xác nhận tại `storage/app/test-results/postman.json`.
- API đăng ký/đăng nhập/đăng xuất bằng token truy cập cá nhân, giỏ hàng và đặt hàng chạy song song; các route giao diện web hiện tại vẫn được giữ nguyên.
- Kịch bản Apache JMeter nằm trong `tests/jmeter/`.
- Tập lệnh OWASP ZAP Baseline và hướng dẫn nằm trong `tests/zap/`.
- Trang `/test-history` hiển thị kết quả đã ghi nhận; PHPUnit/Selenium đọc báo cáo JUnit, Postman đọc báo cáo Newman mới nhất.
- GitHub Actions tự chạy PHPUnit, Newman và Selenium khi push, tạo pull request hoặc khởi chạy thủ công; báo cáo được lưu thành artifact trong 14 ngày.
- JMeter và ZAP tiếp tục chạy thủ công trên môi trường local cô lập. Kết quả local không đại diện cho năng lực sản xuất.

## Tài liệu dự án

- [Hướng dẫn sử dụng và kiểm thử dự án](PROJECT_GUIDE.md)
- [Kế hoạch hoàn thiện và kiểm định](PROJECT_PLAN.md)
- [Tiến độ và kết quả kiểm thử](PROJECT_PROGRESS.md)
- [Hướng dẫn kiểm thử OWASP ZAP](tests/zap/README.md)
