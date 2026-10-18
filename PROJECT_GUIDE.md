# Hướng dẫn sử dụng và kiểm thử dự án

Tài liệu này mô tả cách cài đặt, chạy ứng dụng và sử dụng các công cụ kiểm thử trong môi trường local. Các kết quả kiểm thử chỉ phục vụ thực hành, không đại diện cho khả năng chịu tải hay mức độ an toàn của môi trường production.

## 1. Tổng quan

Dự án là cửa hàng trực tuyến Laravel có giao diện web và API JSON chạy song song:

- Route giao diện web nằm trong `routes/web.php`.
- API sản phẩm, tài khoản/token, giỏ hàng và đơn hàng nằm trong `routes/api.php`.
- PHPUnit kiểm tra logic và các luồng ứng dụng.
- Newman chạy collection Postman trên ứng dụng và SQLite tạm.
- Selenium kiểm thử giao diện trình duyệt trên ứng dụng và SQLite tạm.
- JMeter có kịch bản chỉ đọc và kịch bản checkout có ghi dữ liệu.
- OWASP ZAP chạy Baseline thụ động trên mục tiêu local cô lập.

Không cần bật tất cả công cụ cùng lúc. Để chạy các bài kiểm thử tự động thông thường, PHPUnit, Newman và Selenium tự chuẩn bị cơ sở dữ liệu riêng; chúng không cần ứng dụng đang chạy ở terminal khác.

## 2. Cài đặt và khởi chạy ứng dụng

Mở PowerShell trong thư mục gốc dự án:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
```

Kiểm tra cấu hình `DB_CONNECTION` và `DB_DATABASE` trong `.env` trước khi migrate. Tệp `.env.example` mặc định dùng SQLite; nếu dùng SQLite, đảm bảo đường dẫn cơ sở dữ liệu có thể ghi được. Sau khi xác nhận cấu hình, tạo lược đồ và dữ liệu mẫu:

```powershell
php artisan migrate --seed
```

Khởi động máy chủ:

```powershell
php artisan serve
```

Mặc định ứng dụng sẵn sàng tại `http://127.0.0.1:8000`. Giữ terminal máy chủ mở trong lúc chạy JMeter. Giao diện có thể cần tài nguyên Vite; cài và chạy bằng terminal riêng:

```powershell
npm install
npm run dev
```

`npm run build` tạo tài nguyên giao diện dùng khi cần build:

```powershell
npm run build
```

### Cơ sở dữ liệu và an toàn

- Kiểm tra `.env` trước khi chạy `migrate`, `db:seed` hoặc bất kỳ kịch bản nào ghi dữ liệu. Không đưa bí mật trong `.env` vào Git.
- Không dùng cơ sở dữ liệu production hoặc cơ sở dữ liệu dùng chung cho kiểm thử.
- Newman và Selenium tự tạo, migrate, seed rồi xóa SQLite tạm khi hoàn tất.
- JMeter checkout có chủ đích ghi đơn hàng; chỉ chạy trên cơ sở dữ liệu kiểm thử riêng như hướng dẫn ở mục 7.
- ZAP có sẵn target và SQLite tách biệt; làm theo đúng quy trình trong mục 8.

## 3. PHPUnit

Chạy toàn bộ unit test và feature test:

```powershell
php artisan test
```

`phpunit.xml` đặt môi trường test là `testing`, cơ sở dữ liệu SQLite trong bộ nhớ và xuất báo cáo JUnit vào:

```text
storage/app/test-results/phpunit.xml
```

Các bộ test nằm trong `tests/Unit` và `tests/Feature`. Thêm hoặc chỉnh test ở các thư mục này; chạy riêng một tệp để kiểm tra nhanh:

```powershell
php artisan test tests\Feature\ProductsApiTest.php
```

### Kịch bản ví dụ: API trả đúng hai sản phẩm

Mở `tests/Feature/ProductsApiTest.php`, thêm test bên trong lớp `ProductsApiTest`. Test tạo dữ liệu riêng trong DB test, gọi endpoint danh sách và xác nhận số lượng:

```php
public function test_products_endpoint_returns_each_created_product(): void
{
    foreach (['First test product', 'Second test product'] as $name) {
        Product::create([
            'name' => $name,
            'brand' => 'Test Brand',
            'image' => 'test-product.jpg',
            'price' => 25.00,
        ]);
    }

    $this->getJson(route('api.products.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['name' => 'First test product'])
        ->assertJsonFragment(['name' => 'Second test product']);
}
```

Chạy riêng bộ test:

```powershell
php artisan test tests\Feature\ProductsApiTest.php
```

Ví dụ này thêm một test case mới, không thêm sản phẩm vào DB phát triển. Với luồng checkout, có thể mở `tests/Feature/CheckoutTest.php` và thêm case cho dữ liệu hợp lệ/không hợp lệ, rồi xác nhận cả HTTP/session và trạng thái bảng `orders`/`cart_items`. Không sửa báo cáo XML bằng tay; chạy lại test để sinh kết quả mới.

## 4. Postman và Newman

Chạy toàn bộ collection:

```powershell
npm run test:postman
```

Runner trong `tests/postman/run.js` tự tạo SQLite tạm, migrate, seed sản phẩm, mở ứng dụng ở một cổng loopback khả dụng, chạy Newman rồi dọn dữ liệu tạm. Không cần chạy `php artisan serve` riêng.

- Request, thứ tự luồng và kiểm tra assertion: `tests/postman/laravel_storefront_api.postman_collection.json`.
- Biến collection như `product_id`, `second_product_id`, email và dữ liệu request được khai báo trong chính collection.
- Runner tự ghi đè biến `base_url` bằng URL của máy chủ tạm. Không cần tự sửa `base_url` để chạy runner.
- Thêm/chỉnh request và assertion trong Postman hoặc sửa collection JSON; lưu collection rồi chạy lại Newman.
- Báo cáo chi tiết mỗi request, response và assertion: `storage/app/test-results/postman.json`.

Collection có cả luồng thành công và trường hợp lỗi cho sản phẩm, đăng ký/đăng nhập, giỏ hàng, đặt hàng và quyền truy cập đơn hàng. Một lần chạy đăng ký người dùng trên DB tạm mới, do đó không làm bẩn DB phát triển. Runner thay thế báo cáo JSON bằng kết quả lần chạy mới.

### Kịch bản ví dụ: cập nhật số lượng trong giỏ từ 2 lên 3

Trong Postman mở request **Cart - Update Quantity** ở collection:

1. Giữ nguyên method `PATCH` và URL `/api/cart/items/{{second_cart_item_id}}`.
2. Trong **Body → raw → JSON**, đổi `{"quantity":2}` thành `{"quantity":3}`.
3. Trong tab **Tests** của chính request, đổi assertion `quantity` từ `2` thành `3`.
4. Lưu collection rồi chạy `npm run test:postman`.

Request này phụ thuộc các request trước đó: đăng nhập lấy token và thêm sản phẩm thứ hai để thiết lập `second_cart_item_id`. Chạy nguyên collection bằng runner để các biến được tạo đúng thứ tự. Nếu thêm request mới, đặt nó đúng vị trí trong luồng, thêm header `Accept: application/json` và `Content-Type: application/json` khi có JSON body. Với endpoint yêu cầu đăng nhập, sao chép cấu hình `Authorization` từ một request đã đăng nhập trong collection (token được lấy từ biến `api_token`). Mỗi lần chạy, runner tạo DB mới; request không tác động dữ liệu của ứng dụng đang chạy riêng.

## 5. Selenium

Chạy kiểm thử trình duyệt:

```powershell
npm run test:selenium
```

`tests/selenium/run.js` tự tạo SQLite tạm, nạp sản phẩm và khởi chạy máy chủ trên loopback; không cần khởi chạy Laravel thủ công. Cần cài Google Chrome tương thích với phiên bản Selenium trong `package-lock.json`.

- Các kịch bản và bước trình duyệt nằm trong `tests/selenium/authentication.test.js`.
- Thêm hoặc chỉnh test tại đây, sau đó chạy lại lệnh trên.
- Báo cáo JUnit: `storage/app/test-results/selenium.xml`.
- `SELENIUM_BASE_URL` là biến tùy chọn để trỏ test đến URL khác; runner vẫn khởi động máy chủ riêng. Vì vậy không đặt biến này trong lần chạy mặc định; chỉ dùng khi chủ động cung cấp một URL tương thích.

### Kịch bản ví dụ: form đăng nhập chặn email sai định dạng

Thêm test mới vào `tests/selenium/authentication.test.js`. Ví dụ dưới đây điền email sai định dạng, kiểm tra trình duyệt đánh dấu trường không hợp lệ và không điều hướng khỏi trang đăng nhập:

```javascript
test('login form rejects an invalid email format', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/login`);
    await driver.findElement(By.id('email')).sendKeys('not-an-email');
    await driver.findElement(By.id('password')).sendKeys('password123');
    await driver.findElement(By.css('button[type="submit"]')).click();

    const emailValidity = await driver.executeScript(
        'return document.getElementById("email").validity.typeMismatch;',
    );

    assert.equal(emailValidity, true);
    assert.match(await driver.getCurrentUrl(), /\/login$/);
});
```

Lưu file và chạy `npm run test:selenium`. Test này kiểm tra validation HTML của trình duyệt, không gửi form và không tạo tài khoản. Khi kiểm thử thông báo validation do server trả về, cần gửi form hợp lệ theo cấu trúc của form rồi xác nhận nội dung thông báo trên trang; không coi kiểm tra HTML phía client là thay thế cho kiểm thử server bằng PHPUnit.

## 6. JMeter: kịch bản chỉ đọc

Kịch bản:

```text
tests/jmeter/LaravelStorefrontAPI.jmx
```

Kịch bản gửi các yêu cầu đọc trang/danh sách và chi tiết sản phẩm; không mô phỏng checkout. Cần máy chủ đang chạy, thường tại `http://127.0.0.1:8000`.

Các tham số nhận qua `-J`:

| Tham số | Mặc định | Ý nghĩa |
|---|---:|---|
| `threads` | 10 | Số người dùng ảo đồng thời |
| `ramp` | 5 | Số giây để khởi tạo các người dùng |
| `loops` | 5 | Số vòng lặp mỗi người; `-1` lặp đến hết thời lượng |
| `duration` | 120 | Thời lượng chạy tính bằng giây; Thread Group có bật scheduler |
| `think_time` | 0 | Thời gian nghỉ giữa các yêu cầu, mili giây |
| `host` | `127.0.0.1` | Máy chủ đích |
| `port` | 8000 | Cổng máy chủ đích |
| `PRODUCT_ID` | 1 | Sản phẩm được yêu cầu |

### Ví dụ: tăng từ 10 lên 30 người dùng ảo

Tạo thư mục kết quả riêng và đặt tên file theo lần chạy để không ghi đè:

```powershell
$run = Get-Date -Format "yyyyMMdd-HHmmss"
New-Item -ItemType Directory -Force tests\jmeter\results | Out-Null
jmeter -n -t tests\jmeter\LaravelStorefrontAPI.jmx `
  -Jthreads=30 -Jramp=30 -Jloops=-1 -Jduration=120 -Jthink_time=500 `
  -Jhost=127.0.0.1 -Jport=8000 -JPRODUCT_ID=1 `
  -l "tests\jmeter\results\readonly-30vu-$run.jtl"
```

Ý nghĩa profile trên: tăng dần lên 30 người trong 30 giây, gửi các vòng lặp đọc liên tục trong tối đa 120 giây và nghỉ 500 ms giữa các yêu cầu. Nếu chỉ muốn chạy hữu hạn thay vì lặp theo thời lượng, đặt `-Jloops=5` hoặc một số vòng cụ thể. Không dùng `-Jloops=-1` nếu chưa muốn tải liên tục trong toàn bộ thời lượng.

Để tạo HTML dashboard từ file JTL, dùng một thư mục output mới (JMeter yêu cầu thư mục output chưa tồn tại):

```powershell
jmeter -g "tests\jmeter\results\readonly-30vu-$run.jtl" `
  -o "tests\jmeter\results\dashboard-readonly-30vu-$run"
```

Để thay đổi sản phẩm, đặt `-JPRODUCT_ID=<id>`; giá trị phải tồn tại trong dữ liệu của ứng dụng. Nếu lệnh `jmeter` không có trong `PATH`, gọi trực tiếp `jmeter.bat` trong thư mục `bin` của JMeter, ví dụ:

```powershell
& "C:\tools\apache-jmeter\bin\jmeter.bat" -n -t tests\jmeter\LaravelStorefrontAPI.jmx -Jthreads=30 -Jramp=30 -Jloops=-1 -Jduration=120 -l "tests\jmeter\results\readonly-30vu.jtl"
```

Thay đường dẫn ví dụ bằng nơi đã cài JMeter trên máy.

### Kịch bản ví dụ: chỉ kiểm tra trang sản phẩm ID 2

Nếu ID 2 có trong DB của ứng dụng, giữ nguyên cấu hình test plan và chạy:

```powershell
jmeter -n -t tests\jmeter\LaravelStorefrontAPI.jmx `
  -Jthreads=1 -Jramp=1 -Jloops=1 -Jduration=30 -JPRODUCT_ID=2 `
  -l "tests\jmeter\results\product-2-smoke.jtl"
```

Lệnh thay ID sản phẩm và giảm tải xuống một người dùng, một vòng để smoke-test. Các sampler trang sản phẩm và API chi tiết sử dụng `PRODUCT_ID`; kết quả lưu thành JTL mới. Nếu muốn kiểm tra thêm endpoint, chỉnh/copy HTTP Request sampler và assertion trong JMeter GUI rồi lưu thành `.jmx` mới để giữ bản gốc; bảo đảm request chỉ đọc nếu dùng DB phát triển.

## 7. JMeter: kịch bản checkout

Kịch bản:

```text
tests/jmeter/LaravelStorefrontCheckout.jmx
```

Đây là kịch bản ghi dữ liệu: đăng nhập, thêm sản phẩm vào giỏ và đặt hàng. Mỗi vòng thành công có thể tạo đơn hàng mới; seeder chỉ xóa giỏ hàng của tài khoản kiểm thử, không tự xóa lịch sử đơn. Không chạy trên DB phát triển đang dùng hoặc production.

### Chuẩn bị DB riêng

Seeder từ chối chạy trừ khi `APP_ENV` là `local`/`testing` và tên database có chứa `jmeter`. Tạo DB kiểm thử riêng trước (ví dụ `storefront_jmeter_test`) trong MySQL cục bộ, rồi cấu hình ứng dụng để dùng DB đó. Cách rõ ràng nhất là dùng một tệp env riêng thay vì sửa nhầm `.env` hiện có:

1. Tạo `.env.jmeter` từ `.env.example`.
2. Cấu hình SQLite riêng trong `.env.jmeter`: `APP_ENV=local`, `APP_DEBUG=false`, `DB_CONNECTION=sqlite`, `DB_DATABASE=storage/framework/testing/jmeter.sqlite`. Nếu chọn MySQL thay vì SQLite, tên database cũng phải chứa `jmeter` (ví dụ `storefront_jmeter_test`).
3. Tạo thư mục và tệp SQLite nếu chưa tồn tại. Chỉ làm việc này lần đầu; không xóa tệp DB nếu cần giữ đơn hàng cũ:

```powershell
New-Item -ItemType Directory -Force storage\framework\testing | Out-Null
if (-not (Test-Path storage\framework\testing\jmeter.sqlite)) {
  New-Item -ItemType File storage\framework\testing\jmeter.sqlite | Out-Null
}
```

4. Chạy từng lệnh với `--env=jmeter`, sau khi xác nhận tên DB là DB cô lập:

```powershell
php artisan config:clear
php artisan migrate --env=jmeter --force
php artisan db:seed --env=jmeter --class=AddProductsSeeder --force
php artisan db:seed --env=jmeter --class=JMeterCheckoutSeeder --force
```

Seeder tạo mặc định 100 tài khoản `load-test-1@example.test` đến `load-test-100@example.test`, dùng mật khẩu mặc định khớp với kịch bản, và xóa giỏ hàng cũ của các tài khoản này. Có thể thiết lập trước khi chạy lệnh seed:

```powershell
$env:JMETER_USER_COUNT = "100"
$env:JMETER_PRODUCT_ID = "1"
$env:JMETER_PASSWORD = "Jmeter-Checkout-Only-2026!"
php artisan db:seed --env=jmeter --class=JMeterCheckoutSeeder --force
Remove-Item Env:JMETER_USER_COUNT, Env:JMETER_PRODUCT_ID, Env:JMETER_PASSWORD
```

Nếu dùng mật khẩu khác, truyền đúng mật khẩu đó vào JMeter qua `-Jpassword=<mật_khẩu>`; không ghi mật khẩu thật vào Git. `JMETER_PRODUCT_ID` phải là ID sản phẩm có trong DB kiểm thử.

Khởi động server checkout bằng cùng cấu hình env kiểm thử và giữ terminal mở:

```powershell
php artisan serve --env=jmeter --host=127.0.0.1 --port=8000
```

Chạy thử checkout nhỏ trước, ví dụ 1 user, 1 vòng lặp:

```powershell
$run = Get-Date -Format "yyyyMMdd-HHmmss"
New-Item -ItemType Directory -Force tests\jmeter\results | Out-Null
jmeter -n -t tests\jmeter\LaravelStorefrontCheckout.jmx `
  -Jthreads=1 -Jramp=1 -Jloops=1 -Jduration=120 -Jthink_time=250 `
  -Jhost=127.0.0.1 -Jport=8000 -Jproduct_id=1 `
  -l "tests\jmeter\results\checkout-1vu-$run.jtl"
```

### Tăng checkout lên 30 người dùng

Đảm bảo đã seed ít nhất 30 tài khoản (`JMETER_USER_COUNT=30` trở lên), rồi mới chạy. Khởi đầu bằng một vòng lặp mỗi người để giới hạn số đơn phát sinh:

```powershell
$run = Get-Date -Format "yyyyMMdd-HHmmss"
jmeter -n -t tests\jmeter\LaravelStorefrontCheckout.jmx `
  -Jthreads=30 -Jramp=30 -Jloops=1 -Jduration=120 -Jthink_time=250 `
  -Jhost=127.0.0.1 -Jport=8000 -Jproduct_id=1 `
  -l "tests\jmeter\results\checkout-30vu-$run.jtl"
```

Không chuyển checkout sang `-Jloops=-1` hoặc chạy dài nếu chưa định lượng số đơn mong muốn: mỗi vòng lặp có thể tạo thêm một đơn. Kết quả JTL chỉ ghi ở thư mục kết quả; dữ liệu đơn hàng trong DB cần được xử lý riêng nếu muốn dọn.

**Lưu ý về `--env=jmeter`:** Laravel nạp `.env.jmeter` nếu tệp tồn tại. Hãy kiểm tra cấu hình thực tế trước mọi lệnh migrate/seed. Không dùng `migrate:fresh` hoặc `db:wipe` nếu chưa xác nhận chắc chắn DB đích là DB JMeter có thể xóa.

## 8. OWASP ZAP Baseline

ZAP được cấu hình quét thụ động mục tiêu local riêng; không bật active scan và không gửi form checkout. Cần ZAP 2.17+ cùng PHP/Composer đã cài.

Mở terminal thứ nhất và chạy:

```powershell
.\tests\zap\Start-IsolatedTarget.ps1
```

Giữ tiến trình chạy. Ở terminal thứ hai:

```powershell
.\tests\zap\Invoke-BaselineScan.ps1
```

Target cố định ở `127.0.0.1:8017`; script từ chối host/cổng ngoài loopback đã cho phép. Báo cáo mặc định:

```text
tests/zap/reports/zap-baseline.html
tests/zap/reports/zap-baseline.json
```

Đổi thời lượng spider (giới hạn 1–10 phút) hoặc thư mục báo cáo:

```powershell
.\tests\zap\Invoke-BaselineScan.ps1 -SpiderMinutes 5 -OutputDirectory "tests\zap\reports\run-5min"
```

Nếu ZAP không được cài ở vị trí tự dò, đặt `ZAP_HOME` hoặc truyền `-ZapPath` đến `zap.bat`. Xem [hướng dẫn ZAP](tests/zap/README.md) để biết thêm yêu cầu và ý nghĩa cảnh báo. Không đổi target thành một server dùng chung hoặc production.

### Kịch bản ví dụ: chạy spider 5 phút và giữ báo cáo tách biệt

Sau khi target cô lập đang chạy ở terminal thứ nhất, chạy ở terminal thứ hai:

```powershell
$run = Get-Date -Format "yyyyMMdd-HHmmss"
.\tests\zap\Invoke-BaselineScan.ps1 `
  -SpiderMinutes 5 `
  -OutputDirectory "tests\zap\reports\run-$run"
```

Giới hạn spider là 1–10 phút. Thư mục riêng giúp giữ lại báo cáo mặc định của lần trước. Script vẫn chỉ chấp nhận loopback cổng 8017 và vẫn chạy Baseline thụ động; tăng thời lượng không bật active scan.

## 9. GitHub Actions

Workflow nằm tại `.github/workflows/tests.yml`. Nó tự chạy khi push, pull request hoặc được kích hoạt thủ công trên GitHub. Workflow cài PHP 8.2, Node 22, Chrome rồi chạy PHPUnit, Newman và Selenium; artifacts của `storage/app/test-results/` được giữ 14 ngày.

JMeter và ZAP không nằm trong workflow hiện tại; tiếp tục chạy thủ công trên local. Chỉnh workflow khi thay đổi bước CI hoặc phiên bản runtime; xác nhận kết quả trong tab **Actions** của GitHub sau khi push. Chạy local thành công không đồng nghĩa workflow trên GitHub đã chạy thành công.

### Kịch bản ví dụ: tăng thời gian tối đa của job CI

Trong `.github/workflows/tests.yml`, đổi `timeout-minutes: 20` thành số phút phù hợp, chẳng hạn `30`, nếu suite thực sự cần thêm thời gian. Lưu và push branch; mở tab **Actions**, chọn workflow và kiểm tra log job. Không tăng timeout để che một test bị treo: xem bước đang chạy lâu và log trước. Đổi `php-version` hoặc `node-version` cũng cần xác nhận các dependency hỗ trợ phiên bản mới đó.

## 10. Xem kết quả và lịch sử kiểm thử

Trang lịch sử trong ứng dụng nằm tại `/test-history` (ví dụ `http://127.0.0.1:8000/test-history`). Báo cáo sinh ra:

| Công cụ | Báo cáo |
|---|---|
| PHPUnit | `storage/app/test-results/phpunit.xml` |
| Newman | `storage/app/test-results/postman.json` |
| Selenium | `storage/app/test-results/selenium.xml` |
| ZAP | `tests/zap/reports/zap-baseline.json` và `.html` |
| JMeter | `tests/jmeter/results-20261017-mysql-steady-10vu-120s.jtl` và `tests/jmeter/results-20261017-mysql-checkout-steady-10vu-120s.jtl` |

PHPUnit, Newman, Selenium và ZAP thay thế báo cáo mặc định khi chạy lại. JMeter tạo file mới theo lệnh ở trên và không tự động thay báo cáo JTL đang được trang lịch sử hiển thị. Vì vậy:

1. Giữ file kết quả mới có tên riêng để so sánh và tránh ghi đè.
2. Trang lịch sử hiện tại tham chiếu tới hai file JTL cụ thể; file kết quả mới chưa tự xuất hiện trong trang.
3. Muốn gắn lần chạy JMeter mới vào trang chi tiết/tải báo cáo, cần cập nhật tên JTL tương ứng trong mapping của `app/Http/Controllers/TestHistoryController.php`. Thẻ tổng quan trên trang cũng có thông tin ngày/kết quả tĩnh cần cập nhật riêng. Không đổi tên/ghi đè báo cáo cũ nếu muốn giữ khả năng so sánh.

## 11. Sự cố thường gặp

- **`php` hoặc `composer` không được nhận diện:** cài PHP/Composer và thêm vào `PATH`, rồi mở PowerShell mới.
- **`npm` không được nhận diện hoặc thiếu gói:** cài Node.js; chạy `npm install` lần đầu hoặc `npm ci` khi muốn khớp lockfile.
- **JMeter không tìm thấy lệnh:** gọi `jmeter.bat` bằng đường dẫn đầy đủ như ví dụ ở mục 6.
- **JMeter báo lỗi kết nối:** xác nhận Laravel server vẫn chạy, host/cổng đúng và `BASE_URL` trong kịch bản chỉ đọc trỏ đến ứng dụng local.
- **Sản phẩm không tồn tại:** kiểm tra ID sản phẩm trong DB mà server đang dùng; đổi `PRODUCT_ID`/`product_id` tương ứng.
- **Checkout bị từ chối bởi seeder:** kiểm tra `APP_ENV`, tên DB có chứa `jmeter`, DB đã migrate và `JMETER_PRODUCT_ID` tồn tại.
- **Checkout sai mật khẩu:** bảo đảm `JMETER_PASSWORD` khi seed giống giá trị `-Jpassword` của JMeter.
- **Chrome/Selenium không khởi chạy:** xác nhận Chrome được cài và tương thích với Selenium; chạy lại sau khi đóng các tiến trình trình duyệt bị treo.
- **ZAP không tìm thấy:** kiểm tra bản ZAP (2.17+), `ZAP_HOME`/`-ZapPath` và môi trường Java nếu dùng bản đa nền tảng.
- **Báo cáo lịch sử chưa đổi:** xác nhận lần chạy hoàn tất và sinh báo cáo; JMeter cần cập nhật mapping như giải thích ở mục 10.

## 12. Quy tắc thay đổi tham số

- Ưu tiên truyền tham số khi chạy (`-J...` cho JMeter, biến môi trường cho seeder) thay vì sửa cấu hình mặc định; như vậy dễ lặp lại và so sánh.
- Ghi rõ số user, ramp-up, thời lượng, vòng lặp và think time cùng tên báo cáo để giải thích được kết quả.
- Tách profile chỉ đọc khỏi checkout. Chỉ checkout mới tạo đơn trong các kịch bản JMeter hiện có.
- Không sửa file báo cáo bằng tay để làm thay đổi kết quả. Sửa test/cấu hình, chạy lại và lưu báo cáo mới.
- Không chạy tải JMeter đồng thời với một lần đo khác nếu muốn so sánh công bằng; ghi nhận ứng dụng, DB và các tiến trình nền đang chạy.
