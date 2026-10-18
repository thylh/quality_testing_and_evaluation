# Kế hoạch hoàn thiện và kiểm định cửa hàng trực tuyến Laravel

## 1. Mục đích

Kế hoạch này mô tả phạm vi hoàn thiện cửa hàng trực tuyến, cách kiểm định các luồng chính và tiêu chí xác nhận kết quả. Dự án ưu tiên tính đúng đắn của nghiệp vụ, khả năng kiểm thử lặp lại và an toàn của môi trường kiểm thử. Kế hoạch được cập nhật theo trạng thái hiện tại, không còn mô tả dự án như khi mới bắt đầu.

## 2. Phạm vi dự án

### 2.1. Chức năng nghiệp vụ

- Hiển thị danh sách sản phẩm, thông tin và trang chi tiết sản phẩm.
- Hỗ trợ giỏ hàng của khách và giỏ hàng gắn với tài khoản; kiểm soát quyền sở hữu giỏ hàng.
- Hỗ trợ đăng ký, đăng nhập, đăng xuất và xác thực dữ liệu đầu vào.
- Cung cấp giao diện lập trình ứng dụng đọc danh sách sản phẩm và chi tiết sản phẩm.
- Cho phép người dùng đã đăng nhập đặt hàng bằng phương thức thanh toán khi nhận hàng.
- Lưu thông tin sản phẩm tại thời điểm đặt hàng; giữ nguyên giỏ hàng nếu quy trình đặt hàng thất bại.

### 2.2. Ngoài phạm vi

- Tìm kiếm sản phẩm.
- Phân quyền theo vai trò.
- Tích hợp cổng thanh toán thực tế.
- Cam kết năng lực tải hoặc thỏa thuận mức dịch vụ cho môi trường sản xuất.
- Quét bảo mật chủ động hoặc quét có xác thực cho đến khi phạm vi và dữ liệu kiểm thử được phê duyệt riêng.
- Báo cáo tổng kết chất lượng cuối cùng trong giai đoạn hiện tại; phần này đang được tạm hoãn.

## 3. Mục tiêu kiểm định

- Dùng PHPUnit để kiểm tra nghiệp vụ, xác thực, giỏ hàng, đặt hàng và giao diện lập trình ứng dụng.
- Dùng Selenium để tự động hóa các luồng người dùng quan trọng trên trình duyệt.
- Dùng Postman/Newman để xác nhận mã phản hồi và nội dung của giao diện lập trình ứng dụng.
- Dùng Apache JMeter để đo phản hồi và thông lượng ở một số mức tải, bao gồm luồng chỉ đọc và luồng đặt hàng.
- Dùng OWASP ZAP Baseline để rà soát thụ động cấu hình và nội dung có thể quan sát trên ứng dụng chạy trong môi trường cô lập.
- Lưu kịch bản và kết quả có thể chạy lại; ghi rõ giới hạn môi trường để tránh diễn giải kết quả thử nghiệm cục bộ thành cam kết sản xuất.

## 4. Phạm vi chức năng cần xác nhận

### 4.1. Sản phẩm

- Danh sách và chi tiết sản phẩm lấy dữ liệu nhất quán từ cơ sở dữ liệu.
- Giao diện lập trình ứng dụng trả dữ liệu sản phẩm theo định dạng JSON và mã phản hồi phù hợp.
- Trường hợp mã sản phẩm không tồn tại được xử lý rõ ràng.

### 4.2. Tài khoản và giỏ hàng

- Đăng ký, đăng nhập và đăng xuất hoạt động đúng.
- Dữ liệu đầu vào được kiểm tra; số lượng sản phẩm phải hợp lệ.
- Khách có thể sử dụng giỏ hàng theo phiên; giỏ hàng của tài khoản được giới hạn theo chủ sở hữu.
- Người dùng không thể xem hoặc thay đổi giỏ hàng của tài khoản khác.
- Có thể cập nhật và xóa mặt hàng trong giỏ hàng.

### 4.3. Đặt hàng

- Chỉ người dùng đã đăng nhập mới được tiếp tục đặt hàng.
- Luồng thanh toán khi nhận hàng tạo đơn và bản chụp chi tiết sản phẩm.
- Khi mô phỏng thanh toán thất bại, giỏ hàng được giữ lại để thử lại.
- Kịch bản tải tạo dữ liệu chỉ được chạy trên cơ sở dữ liệu kiểm thử riêng.

## 5. Công cụ và cách sử dụng

### 5.1. PHPUnit

Kiểm thử tự động phía máy chủ cho xác thực, kiểm tra dữ liệu, quyền sở hữu giỏ hàng, đặt hàng và giao diện lập trình ứng dụng. Bộ kiểm thử hiện có 28 bài kiểm thử đạt.

### 5.2. Selenium

Tự động hóa trình duyệt cho các luồng giao diện quan trọng như đăng ký, đăng nhập, điều hướng, giỏ hàng và đặt hàng. Hiện có 7 bài kiểm thử được ghi nhận đạt, bao gồm thử đặt hàng thất bại rồi thực hiện lại thành công.

### 5.3. Postman/Newman

Kiểm tra các điểm cuối sản phẩm, mã phản hồi và nội dung phản hồi. Bộ sưu tập hiện ghi nhận 3 yêu cầu, 6 xác nhận và không có lỗi.

### 5.4. Apache JMeter

- Kịch bản chỉ đọc bao phủ trang chủ cửa hàng, trang sản phẩm, chi tiết sản phẩm, giỏ hàng và giao diện lập trình ứng dụng sản phẩm.
- Kịch bản đặt hàng bao phủ đăng nhập, thêm sản phẩm, kiểm tra trang đặt hàng, tạo đơn thanh toán khi nhận hàng và đăng xuất.
- Kết quả phải báo số yêu cầu, lỗi, thời gian phản hồi trung bình, phân vị 95, thời gian tối đa và thông lượng.
- Dùng cơ sở dữ liệu riêng `storefront_jmeter_test`; không chạy kịch bản ghi dữ liệu trên cơ sở dữ liệu phát triển hoặc sản xuất.
- Kết quả hiện có được tạo bằng máy chủ PHP phát triển trên máy cục bộ, do đó chỉ dùng để kiểm tra chức năng và so sánh trong cùng môi trường; không đại diện cho năng lực sản xuất.

### 5.5. OWASP ZAP

- Chạy ZAP Desktop/Automation Framework cài trực tiếp trên Windows; không yêu cầu Docker.
- Dùng ZAP Baseline: thu thập địa chỉ bằng trình thu thập truyền thống và chờ phân tích thụ động; không bật bộ quét chủ động.
- Chỉ quét `http://127.0.0.1:8017/`, `http://localhost:8017/` hoặc `http://[::1]:8017/`.
- Chuẩn bị cơ sở dữ liệu SQLite riêng tại `storage/framework/testing/zap.sqlite`, nạp dữ liệu sản phẩm kiểm thử và tắt chế độ gỡ lỗi.
- Không gửi biểu mẫu hoặc thực hiện thao tác làm thay đổi dữ liệu. Các đường dẫn đăng nhập, đăng ký, đăng xuất, đặt hàng và thay đổi giỏ hàng nằm ngoài phạm vi trình thu thập.
- Tạo báo cáo HTML và JSON trong `tests/zap/reports/`; báo cáo và cơ sở dữ liệu sinh ra không được đưa vào Git.
- Xác minh từng cảnh báo theo ngữ cảnh; cảnh báo không mặc nhiên chứng minh có thể khai thác.
- Chỉ xem xét quét có xác thực hoặc chủ động sau khi xác định tài khoản thử nghiệm, phạm vi URL, loại trừ thao tác ghi và được chấp thuận riêng.
- Quét thụ động hiện còn cảnh báo thiếu SRI cho bảng định kiểu bên ngoài và cookie `XSRF-TOKEN` không có cờ HttpOnly. Cookie này cần được đọc phía trình duyệt cho luồng chống giả mạo yêu cầu liên trang của Laravel; không được bật HttpOnly một cách máy móc.

## 6. Lộ trình và tiêu chí hoàn thành

### Giai đoạn 1: Khảo sát và xác định phạm vi

**Công việc**

- Rà soát đường dẫn, bộ điều khiển, mô hình dữ liệu, giao diện, lược đồ và các điểm cuối.
- Ghi nhận luồng nghiệp vụ, dữ liệu nhạy cảm và các thao tác có thể thay đổi trạng thái.
- Chốt các chức năng nằm trong và ngoài phạm vi.

**Tiêu chí hoàn thành**

- Có danh sách luồng chính và rủi ro cần kiểm thử.
- Có môi trường và dữ liệu thử nghiệm riêng cho các kịch bản ghi dữ liệu.

### Giai đoạn 2: Hoàn thiện nghiệp vụ cốt lõi

**Công việc**

- Hoàn thiện đăng ký, đăng nhập, đăng xuất và kiểm tra dữ liệu.
- Quản lý giỏ hàng khách và giỏ hàng tài khoản; ngăn truy cập chéo.
- Chuẩn hóa giao diện lập trình ứng dụng sản phẩm.
- Hoàn thiện đặt hàng thanh toán khi nhận hàng và xử lý thất bại.

**Tiêu chí hoàn thành**

- Các luồng sản phẩm, tài khoản, giỏ hàng, giao diện lập trình ứng dụng và đặt hàng hoạt động đúng.
- Lỗi không làm lộ hoặc làm thay đổi dữ liệu của người dùng khác.

### Giai đoạn 3: Kiểm thử phía máy chủ

**Công việc**

- Kiểm thử đăng ký, đăng nhập, đăng xuất, xác thực dữ liệu và quyền sở hữu giỏ hàng.
- Kiểm thử giao diện lập trình ứng dụng và các luồng đặt hàng thành công/thất bại.

**Tiêu chí hoàn thành**

- Bộ kiểm thử PHPUnit chạy thành công.
- Lỗi kiểm thử được xử lý hoặc ghi nhận rõ ràng trước khi chấp nhận kết quả.

### Giai đoạn 4: Kiểm thử giao diện bằng trình duyệt

**Công việc**

- Tự động hóa điều hướng, đăng ký, đăng nhập, giỏ hàng và đặt hàng.
- Xác nhận cả luồng thất bại có thể thử lại.

**Tiêu chí hoàn thành**

- Các kịch bản giao diện chính chạy ổn định trong môi trường đã định.
- Môi trường, trình duyệt và giới hạn của lần chạy được ghi lại.

### Giai đoạn 5: Kiểm thử giao diện lập trình ứng dụng

**Công việc**

- Kiểm tra danh sách sản phẩm, chi tiết sản phẩm, mã phản hồi và nội dung JSON.
- Kiểm tra trường hợp dữ liệu không tồn tại và lỗi đầu vào liên quan.

**Tiêu chí hoàn thành**

- Bộ sưu tập Postman/Newman có thể chạy lại và không có xác nhận thất bại.

### Giai đoạn 6: Kiểm thử hiệu năng bằng JMeter

**Công việc**

- Chạy kịch bản chỉ đọc ở các mức tải phù hợp.
- Chạy kịch bản đặt hàng có ghi dữ liệu trên cơ sở dữ liệu kiểm thử riêng.
- Lưu kết quả và tính các chỉ số phản hồi, lỗi, thông lượng.
- Dọn giỏ hàng thử nghiệm sau khi chạy; lưu ý đơn hàng kiểm thử có thể được tích lũy.

**Tiêu chí hoàn thành**

- Kịch bản có thể chạy lại, thời lượng tự dừng đúng và không nhầm lẫn cơ sở dữ liệu.
- Kết quả ghi rõ đây là phép đo cục bộ, không phải kiểm thử năng lực sản xuất.

### Giai đoạn 6.5: Kiểm thử bảo mật thụ động bằng OWASP ZAP

**Công việc**

- Khởi chạy ứng dụng trên loopback với SQLite riêng và chế độ gỡ lỗi tắt.
- Chạy Baseline trên phạm vi được giới hạn; không quét chủ động và không gửi biểu mẫu.
- Đọc báo cáo, phân loại cảnh báo, sửa cấu hình an toàn và chạy lại để xác minh.
- Ghi lại cảnh báo còn lại, giới hạn máy chủ cục bộ và cảnh báo phụ trợ của ZAP.

**Tiêu chí hoàn thành**

- Tập lệnh từ chối URL ngoài loopback hoặc sai cổng/đường dẫn.
- Không phát sinh đơn hàng hoặc thay đổi dữ liệu nghiệp vụ lâu dài.
- Có báo cáo mới, kết quả được diễn giải theo ngữ cảnh và không che giấu cảnh báo chưa xử lý.

### Giai đoạn 7: Tổng hợp và đánh giá

**Công việc**

- Tổng hợp kết quả PHPUnit, Selenium, Postman/Newman, JMeter và ZAP.
- Phân biệt lỗi đã sửa, rủi ro còn lại, giới hạn môi trường và phần ngoài phạm vi.
- Đưa ra khuyến nghị ưu tiên và kết luận mức sẵn sàng.

**Tiêu chí hoàn thành**

- Báo cáo có phạm vi, phương pháp, môi trường, kết quả, giới hạn và khuyến nghị.
- Không diễn giải kết quả thử nghiệm cục bộ thành chứng nhận hoặc cam kết sản xuất.

## 7. Trạng thái và việc tiếp theo

Các chức năng cốt lõi, kiểm thử PHPUnit, Selenium, Postman/Newman, các kịch bản JMeter và tích hợp ZAP Baseline đã được triển khai. JMeter cần được hiểu là số liệu cục bộ; ZAP còn cảnh báo SRI và một số cảnh báo theo môi trường. Báo cáo tổng hợp cuối cùng đang được tạm hoãn.

Việc tiếp theo khi tiếp tục dự án:

1. Quyết định tự lưu trữ bảng định kiểu hoặc quản lý SRI cho Google Fonts và Font Awesome.
2. Cấu hình `X-Content-Type-Options` cho tệp tĩnh theo máy chủ triển khai thực tế.
3. Nếu cần đánh giá sâu hơn, thiết kế phạm vi và tài khoản riêng trước khi bật quét ZAP có xác thực hoặc chủ động.
4. Khi được yêu cầu, lập báo cáo tổng hợp dựa trên kết quả đã lưu và các giới hạn nêu trên.

## 8. Điều kiện nghiệm thu cuối

- Luồng nghiệp vụ trong phạm vi hoạt động đúng và dữ liệu được bảo vệ theo người dùng.
- Bộ kiểm thử tự động và các bộ kiểm tra công cụ chạy lại được.
- Kết quả hiệu năng và bảo mật có thể truy nguyên đến môi trường, dữ liệu và kịch bản cụ thể.
- Cảnh báo chưa xử lý và giới hạn được công bố rõ ràng.
- Có báo cáo kết luận khi phần tổng hợp được tiếp tục.
