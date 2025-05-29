# Installation Guide

## Prerequisites

- **PHP 7.4+** with the following extensions:
  - PDO
  - PDO_MySQL
  - Sessions
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Web Server** (Apache/Nginx)

## Quick Setup

### 1. Database Configuration

1. Update database credentials in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'ecommerce_db');
   ```

### 2. Automatic Database Setup

Run the setup script to create database and tables:
```bash
php setup.php
```

### 3. Manual Database Setup (Alternative)

If the automatic setup doesn't work:
```bash
mysql -u root -p < database/schema.sql
```

### 4. Web Server Setup

#### Apache
- Ensure `mod_rewrite` is enabled
- The `.htaccess` file is already configured

#### Nginx
Add this to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 5. File Permissions

Ensure the web server can read the files:
```bash
chmod -R 755 /path/to/ecommerce
chown -R www-data:www-data /path/to/ecommerce  # Ubuntu/Debian
```

### 6. Testing

1. Open your web browser
2. Navigate to your domain/localhost
3. You should see the TechStore Pro homepage

## Configuration Options

### Site Settings
Edit `config/config.php` to customize:
- Site name and URL
- Currency settings
- Shipping costs
- Tax rates
- Email settings

### Company Information
- Access the admin panel: `yoursite.com/admin.php`
- Default password: `admin123`
- Update company details that appear throughout the site

## Features Overview

### Customer Features
- ✅ Browse products by category
- ✅ Search products
- ✅ Product detail pages
- ✅ Shopping cart functionality
- ✅ Checkout process
- ✅ Order confirmation

### Admin Features
- ✅ Company information management
- ✅ Basic analytics
- 🔄 Product management (can be added)
- 🔄 Order management (can be added)

### Technical Features
- ✅ Mobile-first responsive design
- ✅ Session-based cart
- ✅ SEO-friendly URLs
- ✅ Error handling
- ✅ Security headers

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify user permissions

2. **404 Errors**
   - Check web server configuration
   - Ensure `.htaccess` is working (Apache)
   - Verify file permissions

3. **Session Issues**
   - Ensure PHP sessions are enabled
   - Check session save path permissions

4. **Images Not Loading**
   - Check file permissions on `assets/` directory
   - Verify image paths in database

### Debug Mode

Enable debug mode in `config/config.php`:
```php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
```

## Security Considerations

### Production Deployment

1. **Change default passwords**
   - Update admin password in `admin.php`

2. **Database security**
   - Use strong database passwords
   - Limit database user permissions
   - Consider using SSL connections

3. **File permissions**
   - Remove write permissions from PHP files
   - Restrict access to config files

4. **SSL/HTTPS**
   - Use SSL certificates in production
   - Update site URLs to HTTPS

5. **Regular updates**
   - Keep PHP and MySQL updated
   - Monitor for security vulnerabilities

## Customization

### Adding New Products
1. Access database directly or create admin interface
2. Insert into `products` table
3. Add product images to `assets/images/products/`

### Styling Changes
- Modify CSS in `includes/header.php`
- Customize Bootstrap variables
- Add custom stylesheets

### Email Integration
1. Configure SMTP settings in `config/config.php`
2. Install PHPMailer or similar library
3. Update order confirmation to send emails

## Support

For technical support or questions:
- Check the README.md file
- Review the code comments
- Contact: contact@techstorepro.com
