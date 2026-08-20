# RentalMobil — Sistem Operasional Rental Kendaraan

Aplikasi Laravel untuk mengelola armada, booking, rental order, serah-terima, GPS BYOK, keuangan, risiko, laporan, blog, dan portal pelanggan.

## Stack

- PHP 8.3+, Laravel 13, Filament 5
- MySQL 8 sebagai database utama dan database test
- Queue database secara default; Redis dapat dipilih saat deployment
- Tailwind CSS, Alpine.js, Chart.js, DomPDF
- Playwright hanya untuk capture screenshot dokumentasi/marketing

## Fitur utama

- Dashboard dan widget berbeda per role
- Kalender armada dan operational command center
- Booking, quotation, order, invoice, pembayaran, deposit, dan approval
- Serah-terima dengan checklist, odometer, tanda tangan, dan 4–12 foto
- Customer portal: pesanan, invoice PDF, dan unggah bukti pembayaran
- Laporan penjualan, keuangan, operasional, serta profitabilitas per kendaraan
- GPS BYOK berbasis database: polling REST, webhook JSON, mapping field, health check, alert, geofence, overspeed, dan perintah berapproval
- Provider pihak ketiga sepenuhnya dinamis; secret terenkripsi dan tidak disimpan di `.env.example`
- Blog, RSS, pSEO, JSON-LD, IndexNow, sitemap index terpecah, dan robots.txt
- Scheduler reminder, overdue escalation, notifikasi, backup, GPS sync, GPS health, retensi log, dan kedaluwarsa dokumen

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Buat dua database MySQL, kemudian isi kredensialnya di `.env`:

```text
DB_DATABASE=rentalmobil
DB_TEST_DATABASE=rentalmobil_testing
```

Lanjutkan setup:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan test
```

Pada Windows, jalankan server pada terminal terpisah:

```powershell
Start-Process powershell -ArgumentList "-NoExit","-Command","cd '$pwd'; `$host.UI.RawUI.WindowTitle='terminal · rentalmobil'; php artisan serve --host=127.0.0.1 --port=8765"
```

Worker dan scheduler:

```bash
php artisan queue:work --queue=gps,default,notifications
php artisan schedule:work
```

## Akun demo

Semua akun memakai password `password`.

| Role | Email |
|---|---|
| Owner | `admin@rentalmobil.test` |
| Manager | `manager@rentalmobil.test` |
| Admin | `admin2@rentalmobil.test` |
| Kasir | `kasir@rentalmobil.test` |
| Driver | `driver@rentalmobil.test` |
| Finance | `finance@rentalmobil.test` |
| Mekanik | `mekanik@rentalmobil.test` |
| Customer portal | `customer@rentalmobil.test` |

## GPS BYOK

1. Tambahkan provider bertipe GPS pada menu **Integrasi → Provider**.
2. Tambahkan **Integrasi GPS BYOK** dan isi format API, base URL, autentikasi, endpoint, mapping field, dan path respons.
3. Hubungkan tracker menggunakan external device ID dari provider.
4. Gunakan webhook unik atau polling queue. Nama merek, command, endpoint, dan parameter tidak dibatasi daftar hardcode.
5. Perintah berisiko masuk ke **Security → Perintah GPS** dan membutuhkan approval pengguna lain.

## Screenshot nyata

Pastikan aplikasi berjalan pada `127.0.0.1:8765` dan akun demo sudah di-seed. Kemudian:

```bash
npx playwright install chromium
npm run screenshots
npm run screenshots:mobile
```

Desktop menghasilkan 22 gambar di `public/marketing/screens/`; mobile menghasilkan 5 gambar di `public/marketing/screens-mobile/`. Landing page otomatis menggunakan gambar nyata ketika file tersedia.

## SEO dan sitemap

- Sitemap index: `/sitemap.xml`
- Sitemap aplikasi: `/sitemap/core-1.xml`
- Sitemap URL dinamis: `/sitemap/custom-{page}.xml`
- RSS: `/blog/feed.xml`
- Dokumentasi publik: `/docs`

Setelah domain produksi aktif, daftarkan `https://domain-anda/sitemap.xml` melalui Google Search Console. URL publik baru dan artikel yang diterbitkan juga dikirim melalui IndexNow; isi key pada `INDEXNOW_KEY` dan sediakan key file publik sesuai deployment.

## Deployment

Gunakan [DEPLOYMENT.md](DEPLOYMENT.md), `deploy/nginx.conf`, dan `deploy/supervisor.conf`. Jangan lupa menjalankan `config:cache`, worker queue, scheduler, HTTPS, backup MySQL, dan pemeriksaan health endpoint.

## Keamanan

- Jangan commit `.env` atau secret provider.
- API key provider dienkripsi at rest.
- Endpoint GPS menggunakan token/signature, throttle, idempotensi, dan audit.
- Portal selalu memeriksa kepemilikan invoice/order.
- Jalankan `composer audit` dan `npm audit` sebelum deployment.

## Lisensi

Proprietary — All rights reserved.
