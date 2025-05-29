# Admin Panel Documentation

## Overview
The eCommerce admin panel provides comprehensive management capabilities for your online store. Built with a modern AdminLTE-style theme, it offers an intuitive interface for managing products, orders, customers, categories, and generating detailed reports.

## Features

### 🔐 Authentication
- Secure admin login with session management
- Modern gradient-styled login form
- Automatic redirect to dashboard after successful login

### 📊 Dashboard
- Real-time statistics overview
- Total products, orders, revenue, and customer counts
- Recent orders summary
- Products by category breakdown
- Quick access to all management sections

### 📦 Product Management
- Complete CRUD operations for products
- **Image Upload System**: Upload and manage product images with:
  - Support for JPEG, PNG, GIF, WebP formats
  - File size validation (5MB limit)
  - Real-time image preview
  - Progress bar during upload
  - Unique filename generation to prevent conflicts
- Category assignment
- Stock quantity tracking
- Featured product designation
- Bulk operations support

### 📋 Order Management
- View all orders with pagination
- Filter orders by status (pending, processing, shipped, delivered, cancelled)
- Update order status with real-time updates
- Detailed order information with customer details
- Order items breakdown
- Status change tracking

### 👥 Customer Management
- Customer information viewing
- Order history per customer
- Customer contact details
- Registration date tracking

### 🏷️ Category Management
- Create, edit, and delete categories
- Category hierarchy management
- Product count per category

### ⚙️ Settings
- Store configuration management
- Company information updates
- System preferences

### 📈 Reports & Analytics
- **Comprehensive Reporting System** with:
  - Sales analytics with date range filtering
  - Interactive charts powered by Chart.js
  - Daily sales trends
  - Order status breakdown
  - Top-selling products analysis
  - Category performance metrics
  - Customer analytics

### 📤 Export Functionality
- **Multiple Export Formats**:
  - CSV exports for spreadsheet analysis
  - PDF reports for printing and sharing
- **Report Types**:
  - Sales Report: Daily sales data with order counts and revenue
  - Products Report: Product performance with sales data
  - Customers Report: Customer analytics with order history
  - Orders Report: Detailed order information with customer data
- Date range selection for all reports
- One-click export with dropdown menu

## Technical Features

### 🛠️ Database Integration
- PDO-based database operations
- Prepared statements for security
- Error handling and transaction support
- Pagination for large datasets

### 🎨 Modern UI/UX
- Bootstrap 5 responsive design
- AdminLTE-inspired styling
- Interactive modals and forms
- Real-time feedback and alerts
- Mobile-friendly interface

### 🔒 Security
- SQL injection prevention with prepared statements
- File upload validation
- Session management
- Input sanitization

### ⚡ Performance
- AJAX-powered interactions
- Efficient database queries
- Image optimization
- Lazy loading for large datasets

## File Structure

```
admin/
├── index.php              # Dashboard with statistics
├── products.php           # Product management with image upload
├── orders.php            # Order management and status updates
├── customers.php         # Customer information management
├── categories.php        # Category CRUD operations
├── settings.php          # Store configuration
├── reports.php           # Analytics and reporting
├── ajax/
│   ├── upload_image.php     # Image upload handler
│   ├── export_report.php    # Report export functionality
│   ├── get_order_details.php   # Order details AJAX
│   └── get_customer_details.php # Customer details AJAX
├── assets/
│   └── admin.css           # Admin panel styling
└── includes/
    ├── header.php          # Common header with navigation
    └── footer.php          # Common footer with scripts
```

## Usage Instructions

### 1. Accessing the Admin Panel
1. Navigate to `http://yourdomain.com/admin.php`
2. Enter your admin credentials
3. You'll be redirected to the dashboard

### 2. Managing Products
1. Go to **Products** section
2. Click **Add New Product** to create products
3. Fill in product details (name, description, price, category, stock)
4. Upload product images using the image upload feature
5. Save the product

### 3. Processing Orders
1. Navigate to **Orders** section
2. View all orders with their current status
3. Click on any order to view details
4. Update order status as needed (pending → processing → shipped → delivered)
5. Orders are automatically tracked with timestamps

### 4. Generating Reports
1. Go to **Reports & Analytics**
2. Select your desired date range
3. View real-time analytics and charts
4. Use the **Export Report** dropdown to download:
   - Sales reports in CSV or PDF format
   - Product performance reports
   - Customer analytics
   - Order summaries

### 5. Managing Categories
1. Access **Categories** section
2. Create new categories for organizing products
3. Edit existing categories
4. View product count per category

### 6. System Settings
1. Navigate to **Settings**
2. Update store information
3. Configure system preferences
4. Manage company details

## Image Upload System

### Supported Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

### Features
- **File Validation**: Automatic format and size checking
- **Size Limit**: 5MB maximum file size
- **Preview**: Real-time image preview before upload
- **Progress**: Upload progress indication
- **Security**: Unique filename generation
- **Storage**: Images stored in `/assets/images/uploads/`

### Usage
1. In the product form, click the file input
2. Select an image file
3. Preview appears automatically
4. Click upload to process
5. Image URL is automatically saved with the product

## Export System

### Available Reports
1. **Sales Report**: Daily/weekly/monthly sales data
2. **Products Report**: Product performance and inventory
3. **Customers Report**: Customer analytics and behavior
4. **Orders Report**: Detailed order information

### Export Formats
- **CSV**: For spreadsheet analysis and data processing
- **PDF**: For printing and formal reporting

### How to Export
1. Set date range in Reports section
2. Click **Export Report** dropdown
3. Select report type and format
4. File downloads automatically

## Database Requirements

### Tables Used
- `products` - Product information and inventory
- `categories` - Product categories
- `orders` - Order information
- `order_items` - Individual order items
- `users` - Customer information
- `company_info` - Store settings

### Key Relationships
- Products belong to categories
- Orders contain multiple order items
- Order items reference products
- Orders belong to users (customers)

## Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive design)

## Maintenance

### Regular Tasks
1. **Monitor disk space** for uploaded images
2. **Archive old reports** if needed
3. **Update product inventory** regularly
4. **Review order statuses** daily
5. **Check system logs** for errors

### Backup Recommendations
1. **Database**: Regular MySQL backups
2. **Images**: Backup `/assets/images/uploads/` folder
3. **Settings**: Export store configuration

## Troubleshooting

### Common Issues
1. **Image upload fails**: Check file permissions on uploads folder
2. **Reports not loading**: Verify database connection
3. **Login issues**: Check session configuration
4. **Export problems**: Ensure sufficient disk space

### Support
For technical support or feature requests, check the main project documentation or contact your system administrator.

---

**Last Updated**: May 29, 2025  
**Version**: 1.0  
**Compatible PHP Version**: 7.4+  
**Database**: MySQL 5.7+
