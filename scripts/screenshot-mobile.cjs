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
  await page.waitForURL(/\/admin/, { timeout: 20000 });
  for (const [name, route] of pages.slice(1)) {
    try {
      try {
        await page.goto(`${baseUrl}${route}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
      } catch {
        await page.waitForTimeout(1500);
        await page.goto(page.url(), { waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {});
      }
      await page.waitForTimeout(2500);
      await page.screenshot({ path: path.join(output, `${name}.png`), fullPage: false });
    } catch (e) {
      console.error(`SKIP ${name}: ${e.message.split('\n')[0]}`);
    }
  }
  await browser.close();
  console.log('Mobile screenshots done in ' + output);
})().catch(error => { console.error(error); process.exit(1); });
