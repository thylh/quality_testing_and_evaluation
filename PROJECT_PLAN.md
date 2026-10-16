# Kế hoạch dự án kiểm định hệ thống Laravel Storefront - Giai đoạn sơ khai

## 1. Bối cảnh ban đầu

Dự án đang ở trạng thái sơ khai, chưa có một kế hoạch kiểm định rõ ràng và chưa được tối ưu hóa theo hướng sản phẩm thương mại điện tử thực tế. Mục tiêu của dự án là xây dựng một hệ thống storefront đơn giản nhưng đủ đáp ứng các yêu cầu nghiệp vụ cơ bản như:

- hiển thị sản phẩm
- xem chi tiết sản phẩm
- quản lý giỏ hàng
- đăng ký và đăng nhập người dùng
- xử lý session và phân quyền cơ bản
- kiểm thử chức năng, giao diện và hiệu năng

Ở giai đoạn đầu, dự án chủ yếu tập trung vào việc triển khai chức năng cơ bản của website và đánh giá chất lượng phần mềm sau đó bằng các công cụ phù hợp.

---

## 2. Mục tiêu tổng thể của dự án

### 2.1. Mục tiêu nghiệp vụ

- Xây dựng website bán hàng đơn giản
- Cho phép người dùng xem danh sách sản phẩm
- Cho phép thêm sản phẩm vào giỏ hàng
- Cho phép đăng ký / đăng nhập / đăng xuất
- Quản lý giỏ hàng theo tài khoản hoặc session
- Bảo vệ dữ liệu và tránh xung đột giữa người dùng với nhau

### 2.2. Mục tiêu kiểm định

- Kiểm thử chức năng bằng PHPUnit
- Kiểm thử giao diện bằng Selenium
- Kiểm thử API bằng Postman
- Kiểm thử hiệu năng bằng JMeter
- Đánh giá chất lượng tổng thể của hệ thống

---

## 3. Yêu cầu chức năng cần có

### 3.1. Chức năng sản phẩm

- Hiển thị danh sách sản phẩm
- Hiển thị hình ảnh, tên, giá, mô tả
- Cho phép xem chi tiết một sản phẩm
- Dữ liệu sản phẩm phải đồng nhất với database

### 3.2. Chức năng giỏ hàng

- Người dùng có thể thêm sản phẩm vào giỏ
- Hệ thống phải kiểm tra số lượng hợp lệ
- Hệ thống phải kiểm tra size / biến thể nếu có
- Người dùng có thể sửa hoặc xóa sản phẩm trong giỏ
- Thanh toán hoặc bước tiếp theo cần được quy định rõ ràng

### 3.3. Chức năng người dùng

- Đăng ký tài khoản
- Đăng nhập bằng email và mật khẩu
- Đăng xuất
- Truy cập các trang có quyền sau khi đăng nhập
- Không cho phép người dùng khác thao tác với giỏ hàng của người khác

### 3.4. Chức năng API

- API lấy danh sách sản phẩm
- API lấy chi tiết sản phẩm theo ID
- Response phải đúng định dạng JSON
- Response code phải chính xác

---

## 4. Tình trạng dự án ở giai đoạn sơ khai

Dự án chưa có sự hoàn thiện đầy đủ theo các tiêu chí sau:

- chưa có hệ thống auth rõ ràng và đầy đủ
- chưa có kiểm soát rõ ràng về quyền sở hữu giỏ hàng
- chưa có API chuẩn hóa
- chưa có kiểm thử tự động đầy đủ
- chưa có kế hoạch kiểm thử hiệu năng
- chưa có báo cáo đánh giá chất lượng hệ thống
- chưa có cấu trúc test rõ ràng với Selenium/JMeter/Postman

Vì vậy, dự án cần được triển khai theo một lộ trình cụ thể để đưa từ trạng thái sơ khai lên mức có thể kiểm định và đánh giá chất lượng chính thức.

---

## 5. Phương án công cụ kiểm thử phù hợp

### 5.1. PHPUnit

- Dùng cho kiểm thử logic backend
- Kiểm tra đăng ký, đăng nhập, giỏ hàng, validation, API
- Phù hợp với Laravel

### 5.2. Selenium

- Dùng cho kiểm thử giao diện người dùng
- Tạo browser automation để test các luồng chính
- Phù hợp cho form đăng ký, đăng nhập, điều hướng, thêm sản phẩm

### 5.3. Postman

- Dùng cho kiểm thử API manual
- Kiểm tra response code, payload và error handling
- Phù hợp cho việc rà soát endpoint sản phẩm

### 5.4. JMeter

- Dùng cho kiểm thử hiệu năng
- Kiểm tra hệ thống khi có nhiều user truy cập cùng lúc
- Đo response time, throughput, error rate, mức độ chịu tải

---

## 6. Lộ trình thực hiện từ đầu

### Giai đoạn 1: Khảo sát và phân tích dự án

Mục tiêu:

- xác định chức năng hiện có
- xác định điểm yếu và thiếu sót
- xác định các module cần chỉnh sửa trước khi kiểm thử

Nhiệm vụ:

- rà soát route, controller, model, view
- rà soát database và migration
- xác định chức năng chưa hoàn thiện
- xác định các trường hợp cần test

Kết quả mong đợi:

- có danh sách chức năng chính và chức năng thiếu
- xác định ít nhất các module cần cải thiện: auth, cart, API, UI

---

### Giai đoạn 2: Hoàn thiện chức năng cốt lõi

Mục tiêu:

- đưa dự án về trạng thái có logic nghiệp vụ cơ bản ổn định

Nhiệm vụ:

- hoàn thiện đăng ký / đăng nhập / đăng xuất
- quản lý session và role base cơ bản
- gắn giỏ hàng với người dùng
- xử lý guest cart hợp lý
- thêm validation cho quantity, size, dữ liệu đầu vào
- chuẩn hóa API đọc sản phẩm

Kết quả mong đợi:

- chức năng chính hoạt động đúng
- dữ liệu không bị xung đột giữa user và session
- app có nền tảng đủ tốt để kiểm thử

---

### Giai đoạn 3: Thiết lập bộ test backend

Mục tiêu:

- đảm bảo logic nghiệp vụ được tự động hóa và dễ kiểm tra

Nhiệm vụ:

- viết test cho đăng ký
- viết test cho đăng nhập
- viết test cho logout
- viết test cho validation
- viết test cho cart ownership
- viết test cho API sản phẩm

Kết quả mong đợi:

- PHPUnit pass cho toàn bộ luồng chức năng chính
- không còn lỗi logic nghiêm trọng ở backend

---

### Giai đoạn 4: Thiết lập Selenium cho UI testing

Mục tiêu:

- kiểm thử trải nghiệm người dùng trên browser

Nhiệm vụ:

- setup Selenium WebDriver
- viết test cho navigation
- test form đăng nhập
- test form đăng ký
- test điều hướng giữa shop, cart, product detail
- test logout

Kết quả mong đợi:

- ứng dụng có thể được test bằng automation UI
- các luồng chính trên browser chạy ổn định

---

### Giai đoạn 5: Kiểm thử API bằng Postman

Mục tiêu:

- xác nhận tính đúng đắn của API

Nhiệm vụ:

- tạo collection cho product API
- xác nhận GET /api/products
- xác nhận GET /api/products/{id}
- xác nhận response code và body
- kiểm tra error handling

Kết quả mong đợi:

- API hoạt động đúng chuẩn
- có dữ liệu kiểm thử rõ ràng cho từng endpoint

---

### Giai đoạn 6: Kiểm thử hiệu năng bằng JMeter

Mục tiêu:

- đánh giá tốc độ và khả năng chịu tải của hệ thống

Nhiệm vụ:

- tạo test plan cho home page
- tạo test plan cho shop page
- tạo test plan cho product detail
- tạo test plan cho cart
- tạo test plan cho API products
- chạy với nhiều mức load khác nhau

Kết quả mong đợi:

- có số liệu về response time, throughput, lỗi hệ thống
- biết hệ thống ổn định ở mức tải nào

---

### Giai đoạn 7: Tổng hợp kết quả và đánh giá chất lượng

Mục tiêu:

- đưa ra nhận định cuối cùng về hệ thống

Nhiệm vụ:

- tổng hợp kết quả từ PHPUnit, Selenium, Postman, JMeter
- xác định thành phần đã đạt yêu cầu
- xác định thành phần tồn tại lỗi hoặc thiếu sót
- đề xuất cải tiến tiếp theo

Kết quả mong đợi:

- báo cáo chất lượng hệ thống rõ ràng
- dự án đạt mức sẵn sàng cho triển khai hoặc thuyết trình

---

## 7. Danh sách công việc còn lại theo ưu tiên

### Ưu tiên cao

- Hoàn thiện auth và session
- Hoàn thiện cart ownership và validation
- Hoàn thiện API sản phẩm
- Viết PHPUnit test cho backend
- Viết Selenium test cho UI cơ bản

### Ưu tiên trung bình

- Tạo Postman collection cho API
- Tạo JMeter script cho hiệu năng
- Kiểm thử flow search và checkout
- Kiểm tra tính nhất quán dữ liệu giữa UI, database và API

### Ưu tiên thấp

- Cải thiện giao diện dữ liệu tĩnh
- Tối ưu hóa trải nghiệm người dùng
- Làm báo cáo kiểm định và đánh giá cuối cùng

---

## 8. Kết luận sơ khai

Dự án ở trạng thái đầu cần được xây dựng theo hướng rõ ràng: chức năng trước, kiểm thử sau. Trước hết, cần hoàn thiện logic nghiệp vụ cốt lõi như auth, cart, API và validation. Sau đó mới triển khai test tự động cho backend và UI. Cuối cùng mới làm kiểm thử hiệu năng và báo cáo tổng kết. Điều này giúp đảm bảo rằng hệ thống không chỉ “chạy được”, mà còn “được kiểm định đúng cách” theo chuẩn phần mềm thực tế.

---

## 9. Mục tiêu cuối cùng của dự án

Đến khi hoàn thành, hệ thống cần đạt các tiêu chí sau:

- chức năng chính hoạt động ổn định
- dữ liệu và session được quản lý đúng
- API trả về dữ liệu chuẩn
- UI có thể được test bằng Selenium
- hiệu năng được kiểm định bằng JMeter
- có báo cáo đánh giá chất lượng và kết luận rõ ràng
