# Deployment Guide — RentalMobil

Panduan lengkap deploy aplikasi RentalMobil ke production server.

---

## 1. Server Requirements

| Komponen | Minimum | Recommended |
|----------|---------|-------------|
| **PHP** | 8.3+ | 8.3+ |
| **Extensions** | mbstring, xml, ctype, json, bcmath, pdo, mysql, zip, gd, curl, fileinfo | + opcache, redis |
| **MySQL** | 8.0+ | 8.0+ |
| **Composer** | 2.x | 2.x |
| **Node.js** | 18+ | 20 LTS |
| **Nginx** | 1.18+ | 1.24+ |
| **Supervisor** | 4.x | 4.x |
| **OS** | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |
| **RAM** | 2 GB | 4 GB+ |

---

## 2. Installation Steps

### 2.1 Install system dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-bcmath php8.3-gd php8.3-curl php8.3-zip php8.3-redis \
  nginx supervisor mysql-server composer nodejs npm certbot python3-certbot-nginx
```

### 2.2 Clone project

```bash
cd /var/www
sudo git clone https://github.com/your-org/rentalmobil.git
sudo chown -R www-data:www-data rentalmobil
cd rentalmobil
```

### 2.3 Install PHP dependencies

```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 2.4 Install Node dependencies & build

```bash
sudo -u www-data npm ci
sudo -u www-data npm run build
```

### 2.5 Environment configuration

```bash
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

Edit `.env` sesuai konfigurasi production (lihat Section 8 — Environment Variables).

### 2.6 Database setup

```bash
# Buat database
sudo mysql -u root -e "CREATE DATABASE rentalmobil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -u root -e "CREATE USER 'rentalmobil'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';"
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON rentalmobil.* TO 'rentalmobil'@'localhost';"
sudo mysql -u root -e "FLUSH PRIVILEGES;"
```

Update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`, lalu:

```bash
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force
```

### 2.7 Storage & permissions

```bash
sudo -u www-data php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 2.8 Optimize

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan icons:cache
```

---

## 3. Web Server Configuration (Nginx)

### 3.1 Copy nginx config

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/rentalmobil
```

Edit `/etc/nginx/sites-available/rentalmobil`:
- Ganti `your-domain.com` dengan domain production
- Pastikan path `fastcgi_pass` sesuai versi PHP-FPM yang terinstall

### 3.2 Enable site

```bash
sudo ln -s /etc/nginx/sites-available/rentalmobil /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4. Queue & Scheduler Setup

### 4.1 Supervisor config

```bash
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/rentalmobil.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rentalmobil-queue:*
sudo supervisorctl start rentalmobil-scheduler
```

### 4.2 Cron (alternative scheduler)

```bash
sudo -u www-data crontab -e
```

Tambah baris:

```
* * * * * cd /var/www/rentalmobil && php artisan schedule:run >> /dev/null 2>&1
```

### 4.3 Verify

```bash
sudo supervisorctl status
# rentalmobil-queue:001    RUNNING   pid 1234
# rentalmobil-scheduler    RUNNING   pid 1235
```

---

## 5. SSL / HTTPS Setup

### 5.1 Certbot

```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
sudo certbot renew --dry-run
```

### 5.2 Auto-renew cron

Certbot sudah install cron otomatis. Verify:

```bash
sudo certbot certificates
```

---

## 6. Performance Optimization

### 6.1 PHP OPcache

Edit `/etc/php/8.3/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.jit=1255
opcache.jit_buffer_size=128M
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

### 6.2 Laravel cache

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 6.3 Redis

Install Redis untuk session, cache, dan queue:

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
```

Update `.env`:

```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

Rebuild cache:

```bash
sudo -u www-data php artisan config:cache
```

### 6.4 Nginx gzip & caching

Sudah include di `deploy/nginx.conf`:
- gzip level 6
- Static file caching 30 hari
- Brotli (opsional, install `ngx_brotli` module)

---

## 7. Troubleshooting

### Common issues

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `storage/logs/laravel.log`, pastikan `.env` benar |
| CSS/JS tidak load | Jalankan `npm run build`, pastikan symlink storage ada |
| Queue tidak jalan | `sudo supervisorctl restart rentalmobil-queue:*` |
| Scheduler tidak jalan | Cek cron `crontab -l`, restart supervisor |
| Permission denied | `sudo chown -R www-data:www-data storage bootstrap/cache` |
| Migration fails | Pastikan database user punya privilege GRANT ALL |
| CSRF token mismatch | Clear browser cookies, pastikan APP_URL benar |

### Logs location

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Nginx log
tail -f /var/log/nginx/rentalmobil_error.log

# Supervisor log
tail -f /var/log/supervisor/rentalmobil-queue.log
tail -f /var/log/supervisor/rentalmobil-scheduler.log
```

---

## 8. Environment Variables

Lihat file `.env.example` di root project untuk daftar lengkap variabel.

### Variables wajib di-set:

| Variable | Deskripsi | Contoh |
|----------|-----------|--------|
| `APP_URL` | URL production | `https://rentalmobil.example.com` |
| `APP_KEY` | Auto-generated | `base64:...` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_DATABASE` | Nama database | `rentalmobil` |
| `DB_USERNAME` | Database user | `rentalmobil` |
| `DB_PASSWORD` | Database password | *(secure password)* |
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `MAIL_MAILER` | Mail transport | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp.mailtrap.io` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `CACHE_STORE` | Cache driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |
| `INDEXNOW_KEY` | IndexNow API key | *(random 32 chars)* |

---

## 9. Post-Deploy Checklist

- [ ] `.env` sudah dikonfigurasi dengan benar
- [ ] `APP_KEY` sudah di-generate
- [ ] `APP_URL` sudah benar (https)
- [ ] Database sudah di-migrate
- [ ] Database sudah di-seed (opsional)
- [ ] `npm run build` sudah dijalankan
- [ ] Storage symlink sudah dibuat
- [ ] File permissions sudah benar
- [ ] OPcache sudah aktif
- [ ] Supervisor queue worker running
- [ ] Supervisor scheduler running
- [ ] SSL certificate installed
- [ ] `certbot renew` cron aktif
- [ ] `robots.txt` accessible
- [ ] `sitemap.xml` accessible
- [ ] IndexNow key configured
- [ ] Mail sending test passed
- [ ] Payment gateway sandbox tested
- [ ] WhatsApp integration tested
