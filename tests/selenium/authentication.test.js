import assert from 'node:assert/strict';
import { after, before, test } from 'node:test';
import { Builder, By, until } from 'selenium-webdriver';
import chrome from 'selenium-webdriver/chrome.js';

const baseUrl = process.env.SELENIUM_BASE_URL ?? 'http://127.0.0.1:8017';
let driver;

before(async () => {
    const options = new chrome.Options().addArguments('--headless=new', '--window-size=1280,900');
    driver = await new Builder()
        .forBrowser('chrome')
        .setChromeOptions(options)
        .build();
});

after(async () => {
    await driver?.quit();
});

test('guest can navigate between registration and login forms', async () => {
    await driver.get(`${baseUrl}/register`);
    await driver.wait(until.elementLocated(By.css('.auth-panel h1')), 5000);
    assert.equal(await driver.findElement(By.css('.auth-panel h1')).getText(), 'Tạo tài khoản');

    await driver.findElement(By.css('.auth-switch a')).click();
    await driver.wait(until.urlMatches(/\/login$/), 5000);
    assert.equal(await driver.findElement(By.css('.auth-panel h1')).getText(), 'Đăng nhập');

    await driver.findElement(By.css('.auth-switch a')).click();
    await driver.wait(until.urlMatches(/\/register$/), 5000);
});

test('login form requires an email and password before submit', async () => {
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

test('registration form requires all account fields before submit', async () => {
    await driver.get(`${baseUrl}/register`);
    await driver.findElement(By.css('button[type="submit"]')).click();

    const validity = await driver.executeScript(`
        return ['name', 'email', 'password', 'password_confirmation']
            .map((id) => document.getElementById(id).validity.valueMissing);
    `);

    assert.deepEqual(validity, [true, true, true, true]);
    assert.match(await driver.getCurrentUrl(), /\/register$/);
});