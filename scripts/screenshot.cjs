const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const baseUrl = process.env.SCREENSHOT_BASE_URL || 'http://127.0.0.1:8765';
const email = process.env.SCREENSHOT_EMAIL || 'admin@rentalmobil.test';
const password = process.env.SCREENSHOT_PASSWORD || 'password';
const output = path.join(__dirname, '..', 'public', 'marketing', 'screens');
const pages = [
  ['dashboard', '/admin'], ['fleet-calendar', '/admin/fleet-calendar'],
  ['command-center', '/admin/operational-command-center'], ['vehicles', '/admin/vehicles'],
  ['vehicle-create', '/admin/vehicles/create'], ['bookings', '/admin/bookings'],
  ['orders', '/admin/rental-orders'], ['customers', '/admin/customers'],
  ['handovers', '/admin/handover-records'], ['gps-map', '/admin/gps-map'],
  ['gps-trackers', '/admin/gps-trackers'], ['gps-alerts', '/admin/gps-alerts'],
  ['gps-integrations', '/admin/gps-integrations'], ['waitlist', '/admin/booking-waitlists'],
  ['invoices', '/admin/invoices'], ['payments', '/admin/payments'],
  ['maintenance', '/admin/maintenance-logs'], ['sales-report', '/admin/laporan-penjualan'],
  ['finance-report', '/admin/laporan-keuangan'], ['operations-report', '/admin/laporan-operasional'],
  ['providers', '/admin/providers'], ['blog', '/admin/blog-posts'],
];

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
  const page = await context.newPage();
  await page.goto(`${baseUrl}/admin/login`, { waitUntil: 'networkidle' });
  await page.locator('input[type="email"]').first().fill('');
  await page.type('input[type="email"]', email, { delay: 35 });
  await page.locator('input[type="password"]').first().fill('');
  await page.type('input[type="password"]', password, { delay: 35 });
  await page.locator('button[type="submit"]').first().click();
  await page.waitForURL(/\/admin(?:\/)?(?:\?.*)?$/, { timeout: 20000 });
  for (const [name, route] of pages) {
    await page.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(output, `${name}.png`), fullPage: false });
  }
  await browser.close();
  console.log(`Captured ${pages.length} desktop screenshots in ${output}`);
})().catch(error => { console.error(error); process.exit(1); });
