# Deployment Guide — RentalMobil

## Server Requirements

| Component | Version |
|-----------|---------|
| OS | Ubuntu 22.04 LTS |
| PHP | 8.3+ |
| MySQL | 8.0+ |
| Redis | 7.0+ |
| Nginx | 1.24+ |
| Node.js | 18+ |
| Composer | 2.7+ |

## Step-by-Step Setup

### 1. Install System Dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip

# PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-redis php8.3-imagick

# MySQL 8
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Nginx
sudo apt install -y nginx

# Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Create Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE rentalmobil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rentalmobil'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON rentalmobil.* TO 'rentalmobil'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Setup Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-repo/rentalmobil.git
sudo chown -R www-data:www-data rentalmobil
cd rentalmobil

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install JS dependencies
npm ci

# Build assets
npm run build

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` with production values:

```env
APP_NAME="RentalMobil"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rentalmobil
DB_USERNAME=rentalmobil
DB_PASSWORD=your_secure_password

SESSION_DRIVER=redis
SESSION_LIFETIME=120

QUEUE_CONNECTION=redis

CACHE_STORE=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
```

### 5. Run Migrations & Seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 6. Setup Storage Link

```bash
php artisan storage:link
```

### 7. Set Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo find storage -type d -exec chmod 775 {} \;
sudo find bootstrap/cache -type d -exec chmod 775 {} \;
```

## Nginx Configuration

Copy the nginx config:

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/rentalmobil
sudo ln -s /etc/nginx/sites-available/rentalmobil /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

## SSL Setup (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
sudo certbot renew --dry-run
```

## Queue Worker Setup

### Using Supervisor

```bash
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/rentalmobil.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rentalmobil-*
```

### Verify Workers

```bash
sudo supervisorctl status
```

## Scheduler Setup

Add to crontab:

```bash
sudo crontab -e
```

```
* * * * * cd /var/www/rentalmobil && php artisan schedule:run >> /dev/null 2>&1
```

## Backup Configuration

### Automated Backups

The application includes a `db:backup` command that runs daily at 02:00 via the scheduler. Backups are stored in `storage/backups/` and automatically clean up files older than 7 days.

### Manual Backup

```bash
php artisan db:backup
```

### Setup Remote Backup (Optional)

```bash
# Add to crontab for off-site backup
0 3 * * * cd /var/www/rentalmobil && php artisan db:backup && rclone copy storage/backups/ remote:rentalmobil-backups/ --max-age 7d
```

## Monitoring

### Log Monitoring

```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log
```

### Process Monitoring

```bash
# Check all services
sudo systemctl status nginx
sudo systemctl status mysql
sudo systemctl status redis-server
sudo supervisorctl status
```

### Health Check

```bash
curl -s https://your-domain.com/health || echo "Application is down!"
```

## Performance Optimization

### OPcache

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
```

```bash
sudo systemctl restart php8.3-fpm
```

### Redis Cache

Ensure Redis is configured for session and cache:

```bash
redis-cli ping
# Should return: PONG
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 502 Bad Gateway | Check PHP-FPM: `sudo systemctl status php8.3-fpm` |
| Permission denied | Run `sudo chown -R www-data:www-data storage bootstrap/cache` |
| Migration fails | Check `.env` DB credentials and ensure MySQL is running |
| Queue not processing | Check Supervisor: `sudo supervisorctl status` |
| Assets not loading | Run `npm run build` and verify `public/build/` exists |
| SSL not working | Verify certbot: `sudo certbot certificates` |

## Post-Deployment Checklist

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate `APP_KEY`
- [ ] Configure SSL certificate
- [ ] Set up queue workers with Supervisor
- [ ] Set up scheduler in crontab
- [ ] Configure backup strategy
- [ ] Set correct file permissions
- [ ] Test all payment methods
- [ ] Verify notification channels
- [ ] Set up monitoring/alerting
- [ ] Submit sitemap to Google Search Console
- [ ] Test robots.txt access
