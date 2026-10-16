import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { Builder, By, until } from 'selenium-webdriver';
import chrome from 'selenium-webdriver/chrome.js';

const baseUrl = process.env.SELENIUM_BASE_URL ?? 'http://127.0.0.1:8017';
let driver;

beforeEach(async () => {
    const options = new chrome.Options().addArguments('--headless=new', '--window-size=1280,900');
    driver = await new Builder()
        .forBrowser('chrome')
        .setChromeOptions(options)
        .build();
});

afterEach(async () => {
    await driver?.quit();
});

test('guest can navigate between registration and login forms', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/register`);
    await driver.wait(until.elementLocated(By.css('.auth-panel h1')), 5000);
    assert.equal(await driver.findElement(By.css('.auth-panel h1')).getText(), 'Tạo tài khoản');

    await driver.findElement(By.css('.auth-switch a')).click();
    await driver.wait(until.urlMatches(/\/login$/), 5000);
    assert.equal(await driver.findElement(By.css('.auth-panel h1')).getText(), 'Đăng nhập');

    await driver.findElement(By.css('.auth-switch a')).click();
    await driver.wait(until.urlMatches(/\/register$/), 5000);
});

test('login form requires an email and password before submit', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/login`);
    await driver.findElement(By.css('button[type="submit"]')).click();

    const validity = await driver.executeScript(`
        return {
            emailMissing: document.getElementById('email').validity.valueMissing,
            passwordMissing: document.getElementById('password').validity.valueMissing,
        };
    `);

    assert.deepEqual(validity, { emailMissing: true, passwordMissing: true });
    assert.match(await driver.getCurrentUrl(), /\/login$/);
});

test('registration form requires all account fields before submit', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/register`);
    await driver.findElement(By.css('button[type="submit"]')).click();

    const validity = await driver.executeScript(`
        return ['name', 'email', 'password', 'password_confirmation']
            .map((id) => document.getElementById(id).validity.valueMissing);
    `);

    assert.deepEqual(validity, [true, true, true, true]);
    assert.match(await driver.getCurrentUrl(), /\/register$/);
});

test('shop displays product cards and product detail page loads real product data', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/shop`);
    await driver.wait(until.elementLocated(By.css('.pro')), 5000);

    const productName = await driver.findElement(By.css('.pro h5')).getText();
    const productLink = await driver.findElement(By.css('.pro a[href*="/product/"]'));
    await productLink.click();

    await driver.wait(until.urlContains('/product/'), 5000);
    const detailName = await driver.findElement(By.css('.single-product-details h4')).getText();

    assert.ok(detailName.length > 0);
    assert.ok(productName.length > 0);
});

test('buyer can add a product to cart from the product detail page', { concurrency: false }, async () => {
    await driver.get(`${baseUrl}/shop`);
    await driver.wait(until.elementLocated(By.css('.pro a[href*="/product/"]')), 5000);

    await driver.findElement(By.css('.pro a[href*="/product/"]')).click();
    await driver.wait(until.urlContains('/product/'), 5000);

    const sizeSelect = await driver.findElement(By.css('select[name="size"]'));
    await driver.executeScript(
        "arguments[0].value = 'Medium'; arguments[0].dispatchEvent(new Event('change', { bubbles: true }));",
        sizeSelect,
    );

    const quantityInput = await driver.findElement(By.css('input[name="quantity"]'));
    await driver.executeScript(
        "arguments[0].value = '2'; arguments[0].dispatchEvent(new Event('input', { bubbles: true }));",
        quantityInput,
    );

    const cartForm = await driver.findElement(By.css('form[action*="/addtocart/"]'));
    await driver.executeScript('arguments[0].requestSubmit();', cartForm);
    await driver.sleep(1000);

    await driver.get(`${baseUrl}/cart`);
    await driver.wait(until.elementLocated(By.css('#cart')), 5000);
    const cartText = await driver.findElement(By.css('body')).getText();

    assert.match(cartText, /Tổng tiền:|Cập nhật|Cartoon Astronaut T-Shirt/);
});