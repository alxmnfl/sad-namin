# 🛠️ Abeth Hardware POS System

A complete Point of Sale (POS) system for hardware stores with customer ordering, inventory management, and transaction tracking.

## 📋 Features

- **Customer Portal** - Browse products, add to cart, checkout with pickup/delivery options
- **POS Interface** - Quick sales processing for staff
- **Admin Dashboard** - Product management, inventory tracking, order monitoring
- **Mobile Responsive** - Full mobile support with optimized layouts
- **Transaction History** - Complete purchase tracking and reporting

## 🚀 Quick Setup Guide

### Prerequisites

- **XAMPP** (Apache, MySQL, PHP 7.4+)
- **Web Browser** (Chrome, Firefox, Edge recommended)
- **Git** (optional, for cloning)

### Installation Steps

#### 1. Download/Clone Repository

**Option A: Clone with Git**
```bash
git clone https://github.com/djmurill0/sad-namin.git
cd sad-namin
```

**Option B: Download ZIP**
- Download repository as ZIP
- Extract to `C:\xampp\htdocs\SAD_POS`

#### 2. Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** module
3. Start **MySQL** module

![XAMPP Control](https://via.placeholder.com/600x200/ffcc00/000000?text=Start+Apache+and+MySQL)

#### 3. Create Database

**Method 1: Using phpMyAdmin (Recommended)**

1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Click **Choose File** and select `database.sql` from project folder
4. Click **Go** button at bottom
5. Wait for success message

**Method 2: Manual SQL Execution**

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click **SQL** tab
3. Copy entire contents of `database.sql`
4. Paste into SQL query box
5. Click **Go**

#### 4. Configure Database Connection (Optional)

If your MySQL settings differ from defaults:

1. Open `config.php` in text editor
2. Update these values:
```php
define('DB_HOST', 'localhost');    // Usually localhost
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password (blank by default)
define('DB_NAME', 'hardware_db');  // Database name
```
3. Save file

#### 5. Access the System

Open browser and navigate to:

- **Customer Store**: `http://localhost/SAD_POS/index.php`
- **Admin Dashboard**: `http://localhost/SAD_POS/admin.php`
- **POS Interface**: `http://localhost/SAD_POS/pos.php`

## 🔐 Default Login Credentials

### Admin Account
- **Email**: `admin@abeth.com`
- **Password**: `admin123`

### Staff Account
- **Email**: `staff@abeth.com`
- **Password**: `admin123`

### Customer Account
- **Email**: `customer@example.com`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change these passwords after first login!

## 📧 Email Configuration (Optional)

For order confirmation emails:

1. Open `config.php`
2. Add your email settings:
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
```

3. Enable PHP's `sendmail` in `php.ini`:
```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
```

## 🗂️ Project Structure

```
SAD_POS/
├── index.php              # Customer storefront
├── admin.php              # Admin dashboard
├── pos.php                # Point of sale interface
├── products.php           # Product browsing
├── login.php              # Authentication
├── config.php             # Database configuration
├── database.sql           # Database schema & sample data
├── css/                   # Stylesheets
│   ├── index.css
│   ├── admin.css
│   ├── products.css
│   └── pos.css
├── ajax/                  # AJAX endpoints
│   ├── add_to_cart_ajax.php
│   ├── edit_product_ajax.php
│   ├── update_stock_ajax.php
│   └── delete_product_ajax.php
└── images/                # Product images
```

## 🛠️ Troubleshooting

### Database Connection Error

**Error**: "Connection failed: Access denied"

**Solution**:
1. Check MySQL is running in XAMPP
2. Verify credentials in `config.php`
3. Ensure database `hardware_db` exists

### Apache Won't Start

**Error**: Port 80 already in use

**Solution**:
1. Open XAMPP Config for Apache
2. Change port from 80 to 8080
3. Access via `http://localhost:8080/SAD_POS/`

### Blank Page or Errors

**Solution**:
1. Check Apache error logs in XAMPP
2. Ensure PHP version is 7.4+
3. Verify all files uploaded correctly

### Email Not Sending

**Solution**:
1. Gmail requires App Passwords (not regular password)
2. Enable "Less secure app access" in Gmail settings
3. Check `sendmail` configuration in `php.ini`

## 📱 Mobile Usage

System is fully responsive:
- Full-width burger menus
- 2-column product grids
- Touch-optimized buttons
- Card-based layouts

## 🔄 Updating Products

### Via Admin Dashboard

1. Login as admin/staff
2. Navigate to Products section
3. Click Edit/Delete or Add New Product
4. Changes reflect immediately

### Via Database

1. Open phpMyAdmin
2. Select `hardware_db` database
3. Browse `products` table
4. Edit entries directly

## 📊 Database Tables

- **users** - Customer, staff, admin accounts
- **products** - Inventory items
- **cart** - Active shopping carts
- **transactions** - Completed orders
- **transaction_items** - Order line items

## 🤝 Contributing

This is a student project for SAD (Systems Analysis and Design).

## 📄 License

Educational use only.

## 💡 Support

For issues or questions:
1. Check troubleshooting section above
2. Verify XAMPP services are running
3. Check database connection in config.php
4. Review Apache/PHP error logs

---

**Version**: 1.0  
**Last Updated**: 2024  
**Developed for**: XAMPP Environment (Windows)
