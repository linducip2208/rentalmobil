# RentalMobil — Sistem Rental Mobil

Aplikasi manajemen rental mobil berbasis Laravel dengan admin panel Filament, customer portal, dan fitur lengkap untuk operasional bisnis rental kendaraan.

## Features

### Master Data
- Manajemen kendaraan (10+ kategori: SUV, Sedan, MPV, Pickup, Electric)
- Brand & kategori kendaraan
- Lokasi (multi-cabang: Jakarta, Bandung, Surabaya)
- Customer & driver management
- Addon services (Asuransi, Supir, Baby Seat, GPS)
- Payment methods (Transfer Bank, Cash, QRIS)

### Transaksi
- Booking & order management
- Invoice otomatis dari return record
- Payment processing & tracking
- Surge pricing rules

### Operasional
- Return processing dengan inspeksi kendaraan
- Maintenance log & service scheduling
- Fuel log & kilometer tracking
- Delivery management (one-way rental)
- GPS tracking integration

### Keamanan
- Blacklist & watch list
- Trust score system untuk customer
- Investigation case management
- Police report auto-generation
- Audit log lengkap

### Keuangan
- Chart of Accounts (COA)
- Journal entries & accounting
- Expense management
- Bank account tracking
- P&L reports

### Marketing
- Blog & article management
- FAQ management
- Promo voucher system
- Newsletter subscribers
- Testimonial management

### Integrasi
- Dynamic provider system (WhatsApp, SMS, Email, Telegram)
- Webhook support
- Notification queue dengan retry mechanism

### Laporan
- Dashboard per-role (Admin, Manager, Customer)
- Sales & revenue reports
- Vehicle utilization reports
- Financial reports (COA, journal)
- PDF export

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11 |
| Admin Panel | Filament v3 |
| Database | MySQL 8 |
| Cache | Redis |
| Queue | Redis + Horizon |
| Frontend | TailwindCSS + Alpine.js |
| Charts | Chart.js |
| PDF | DomPDF |
| Icons | Heroicons + Font Awesome |
| PHP | 8.3+ |

## Installation

### Prerequisites
- PHP 8.3+
- MySQL 8.0+
- Redis
- Composer
- Node.js 18+

### Setup

```bash
# Clone repository
git clone https://github.com/your-repo/rentalmobil.git
cd rentalmobil

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then run:
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@rentalmobil.test | password |

## API Documentation

API endpoints available at `/api/` prefix. Authentication via Laravel Sanctum.

### Public Endpoints
- `GET /api/vehicles` — List available vehicles
- `GET /api/vehicles/{id}` — Vehicle detail
- `GET /api/locations` — List locations
- `GET /api/categories` — List categories
- `GET /api/brands` — List brands
- `GET /api/faqs` — List FAQs

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for comprehensive deployment guide.

## License

Proprietary — All rights reserved.
