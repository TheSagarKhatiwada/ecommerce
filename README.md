# eCommerce Website

A mobile-first, dynamic eCommerce website built with PHP, MySQL, and Bootstrap 5.

## Features

### 🔹 Core Features
- Responsive, mobile-first design using Bootstrap 5 components
- Homepage with featured products and banners
- Category-wise product browsing
- Product detail pages with comprehensive information
- Search functionality for finding products

### 🛒 Cart & Order Features
- Shopping cart with add/remove/update functionality
- Quantity management
- Total cost calculation
- Checkout form with customer information collection
- Order processing and database storage
- Order confirmation page

### 🏢 Dynamic Company Info
- Database-stored company details
- Dynamic display in header, footer, and contact page
- Easy updating through database records

## Technical Specifications

- **Backend**: Pure PHP (no frameworks)
- **Database**: MySQL
- **Frontend**: Bootstrap 5, responsive design
- **Session Management**: PHP sessions for cart functionality

## Database Schema

The application uses the following tables:
- `categories` - Product categories
- `products` - Product information
- `orders` - Customer orders
- `order_items` - Order line items
- `company_info` - Company details

## Installation

1. **Database Setup**:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Configuration**:
   - Update database credentials in `config/database.php`
   - Ensure PHP sessions are enabled

3. **Web Server**:
   - Place files in web server document root
   - Ensure PHP and MySQL are running

## File Structure

```
ecommerce/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── classes.php          # Core PHP classes
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── assets/
│   └── images/
│       └── products/        # Product images
├── database/
│   └── schema.sql           # Database schema and sample data
├── index.php                # Homepage
├── product.php              # Product details
├── category.php             # Category listing
├── search.php               # Search results
├── cart.php                 # Shopping cart
├── cart_handler.php         # Cart AJAX handler
├── checkout.php             # Checkout form
├── order_confirmation.php   # Order confirmation
└── contact.php              # Contact page
```

## Key Classes

- **Database**: PDO wrapper for database operations
- **Product**: Product management and retrieval
- **Category**: Category management
- **Cart**: Session-based shopping cart
- **Order**: Order processing
- **CompanyInfo**: Company information management

## Features Overview

### Homepage
- Hero section with company branding
- Featured products showcase
- Category navigation
- Company features highlight

### Product Management
- Featured product marking
- Stock quantity tracking
- Category organization
- Image support with fallbacks

### Shopping Cart
- Session-based storage
- AJAX updates
- Quantity management
- Price calculations including tax and shipping

### Checkout Process
- Customer information collection
- Order total calculation
- Secure form processing
- Email confirmation (demo mode)

### Search & Navigation
- Product search by name and description
- Category-based browsing
- Responsive navigation menu
- Breadcrumb navigation

## Demo Data

The schema includes sample data for:
- 5 product categories (Electronics, Clothing, Books, Home & Garden, Sports)
- 15+ sample products with various price points
- Company information for "TechStore Pro"

## Mobile-First Design

- Responsive Bootstrap 5 components
- Mobile-optimized navigation
- Touch-friendly interface
- Optimized product cards and layouts

## Security Features

- Prepared statements for SQL queries
- Input validation and sanitization
- XSS protection with htmlspecialchars
- Session security

## Browser Support

- Modern browsers with CSS Grid and Flexbox support
- Mobile browsers (iOS Safari, Chrome Mobile)
- Progressive enhancement approach