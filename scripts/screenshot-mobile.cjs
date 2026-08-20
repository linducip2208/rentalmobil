const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const baseUrl = process.env.SCREENSHOT_BASE_URL || 'http://127.0.0.1:8765';
const output = path.join(__dirname, '..', 'public', 'marketing', 'screens-mobile');
const pages = [['login','/admin/login'],['dashboard','/admin'],['vehicles','/admin/vehicles'],['vehicle-create','/admin/vehicles/create'],['gps-map','/admin/gps-map']];

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 414, height: 896 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  await page.goto(`${baseUrl}/admin/login`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: path.join(output, 'login.png'), fullPage: false });
  await page.locator('input[type="email"]').first().fill('');
  await page.type('input[type="email"]', process.env.SCREENSHOT_EMAIL || 'admin@rentalmobil.test', { delay: 35 });
  await page.locator('input[type="password"]').first().fill('');
  await page.type('input[type="password"]', process.env.SCREENSHOT_PASSWORD || 'password', { delay: 35 });
  await page.locator('button[type="submit"]').first().click();
  await page.waitForURL(/\/admin(?:\/)?(?:\?.*)?$/, { timeout: 20000 });
  for (const [name, route] of pages.slice(1)) {
    await page.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(output, `${name}.png`), fullPage: false });
  }
  await browser.close();
  console.log(`Captured ${pages.length} mobile screenshots in ${output}`);
})().catch(error => { console.error(error); process.exit(1); });
