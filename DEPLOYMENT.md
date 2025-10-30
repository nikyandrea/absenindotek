# Panduan Deployment Sistem Absensi IndoTek

## Persyaratan Sistem

### PHP Requirements
- **PHP Version**: 8.2 atau lebih tinggi
- **Required Extensions**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD (untuk face recognition)
  - SQLite3 atau MySQL (tergantung database)

### Server Requirements
- **Web Server**: Apache 2.4+ atau Nginx 1.18+
- **Memory**: Minimum 512MB RAM (Recommended 1GB+)
- **Storage**: Minimum 500MB (untuk aplikasi + database)
- **SSL Certificate**: Disarankan untuk HTTPS

### Database Options
- **SQLite**: Untuk deployment kecil-menengah (1-100 karyawan)
- **MySQL/MariaDB 5.7+**: Untuk deployment besar (100+ karyawan)

---

## Langkah-Langkah Deployment

### 1. Persiapan Server

#### Upload Files
Upload semua file aplikasi ke server menggunakan FTP/SFTP:
```bash
# Upload ke: /home/username/public_html/absenindotek/
# Atau: /var/www/html/absenindotek/
```

#### Set File Permissions
```bash
# Masuk ke direktori aplikasi
cd /path/to/absenindotek

# Set ownership (ganti 'www-data' dengan user web server Anda)
sudo chown -R www-data:www-data .

# Set permissions
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

---

### 2. Konfigurasi Environment

#### Copy dan Edit .env File
```bash
# Copy .env.example menjadi .env
cp .env.example .env

# Edit file .env
nano .env  # atau gunakan editor lain
```

#### Konfigurasi Penting di .env

**Basic Configuration:**
```env
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_KEY=  # Akan di-generate nanti
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

**Database Configuration (pilih salah satu):**

*Option A: SQLite (Recommended untuk deployment awal)*
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

*Option B: MySQL*
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absenindotek
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password
```

**Mail Configuration (opsional):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
```

---

### 3. Install Dependencies

```bash
# Install Composer dependencies (production mode)
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Clear dan cache configuration
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 4. Setup Database

#### Untuk SQLite:
```bash
# Buat file database
touch database/database.sqlite

# Set permission
chmod 664 database/database.sqlite
chmod 775 database

# Run migrations
php artisan migrate --force

# (Opsional) Seed data awal
php artisan db:seed --force
```

#### Untuk MySQL:
```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE absenindotek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absen_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON absenindotek.* TO 'absen_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# (Opsional) Seed data awal
php artisan db:seed --force
```

---

### 5. Konfigurasi Web Server

#### Apache Configuration

**Create Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /path/to/absenindotek/public

    <Directory /path/to/absenindotek/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/absenindotek-error.log
    CustomLog ${APACHE_LOG_DIR}/absenindotek-access.log combined
</VirtualHost>
```

**Enable Modules:**
```bash
sudo a2enmod rewrite
sudo a2ensite absenindotek.conf
sudo systemctl restart apache2
```

#### Nginx Configuration

**Create Server Block:**
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/absenindotek/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Restart Nginx:**
```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

### 6. Setup SSL Certificate (Recommended)

#### Using Let's Encrypt (Free):
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # Untuk Apache
# atau
sudo apt install certbot python3-certbot-nginx   # Untuk Nginx

# Generate Certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
# atau
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Update .env
APP_URL=https://yourdomain.com
```

---

### 7. Build Frontend Assets

```bash
# Install Node dependencies
npm install

# Build for production
npm run build

# Verify build output
ls -la public/build/
```

---

### 8. Setup Cron Jobs (untuk scheduled tasks)

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/absenindotek && php artisan schedule:run >> /dev/null 2>&1
```

---

### 9. Setup Queue Worker (opsional, untuk background jobs)

**Create Systemd Service:**
```bash
sudo nano /etc/systemd/system/absenindotek-worker.service
```

**Service Configuration:**
```ini
[Unit]
Description=Sistem Absensi Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/absenindotek/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

**Enable and Start:**
```bash
sudo systemctl enable absenindotek-worker
sudo systemctl start absenindotek-worker
sudo systemctl status absenindotek-worker
```

---

### 10. Verifikasi Deployment

#### Checklist:
- [ ] Website dapat diakses via browser
- [ ] SSL certificate aktif (HTTPS)
- [ ] Login page tampil dengan benar
- [ ] Static assets (CSS, JS, gambar) ter-load
- [ ] Database connection berhasil
- [ ] Tidak ada error di log: `tail -f storage/logs/laravel.log`
- [ ] File permissions sudah benar
- [ ] Cron jobs berjalan (check `storage/logs/laravel.log`)

#### Test Login:
- **Admin Default** (jika seed dijalankan):
  - Email: `admin@example.com`
  - Password: `password`

---

## Maintenance & Troubleshooting

### Monitoring Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

### Clear Cache (jika ada masalah)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Aplikasi
```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart apache2  # atau nginx
sudo systemctl restart absenindotek-worker  # jika ada
```

### Backup Database

**SQLite:**
```bash
# Manual backup
cp database/database.sqlite database/backup-$(date +%Y%m%d).sqlite

# Automated backup script
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)
cp /path/to/absenindotek/database/database.sqlite $BACKUP_DIR/database-$DATE.sqlite
find $BACKUP_DIR -mtime +30 -delete  # Hapus backup >30 hari
```

**MySQL:**
```bash
# Manual backup
mysqldump -u username -p absenindotek > backup-$(date +%Y%m%d).sql

# Automated backup
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p'password' absenindotek | gzip > $BACKUP_DIR/database-$DATE.sql.gz
find $BACKUP_DIR -mtime +30 -delete
```

---

## Security Best Practices

1. **Jangan commit .env ke Git**
   - Sudah ada di `.gitignore`
   - Generate APP_KEY baru di setiap server

2. **Set APP_DEBUG=false di production**
   - Mencegah informasi sensitif ter-expose

3. **Update Dependencies Regularly**
   ```bash
   composer update --with-all-dependencies
   npm update
   ```

4. **Monitor Failed Login Attempts**
   - Check `storage/logs/laravel.log` untuk suspicious activity

5. **Restrict Database Access**
   - Hanya allow dari localhost jika server tunggal
   - Gunakan firewall rules

6. **Regular Backups**
   - Setup automated daily backups
   - Test restore procedure

---

## Contact & Support

Untuk pertanyaan atau masalah deployment:
- Check `TROUBLESHOOTING.md` untuk common issues
- Review Laravel documentation: https://laravel.com/docs

---

**Last Updated:** 2025-01-29
**Version:** 1.0.0
