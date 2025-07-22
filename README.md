# Worker Document Management System

A complete document management system built with PHP, MySQL, and modern CSS. Perfect for small to medium organizations to manage their documents efficiently.

## 🚀 Features

### ✅ Essential Modules (RM250 Budget)

#### 1. Basic Authentication System (RM80)
- ✅ HTML login form with PHP validation
- ✅ MySQL user table for authentication
- ✅ Basic session management
- ✅ Simple user verification
- ✅ Logout functionality

#### 2. Document CRUD Operations (RM100)
- ✅ **Create**: Add document information (name, description, category)
- ✅ **Read**: View document list in table format
- ✅ **Update**: Edit document information
- ✅ **Delete**: Remove documents
- ✅ Basic file upload functionality (PDF, DOC, images)

#### 3. Database & Backend (RM40)
- ✅ MySQL database with 4 tables
- ✅ Users table
- ✅ Documents table
- ✅ Categories table
- ✅ PHP-MySQL connection scripts

#### 4. User Interface & Navigation (RM30)
- ✅ Combined HTML+PHP files
- ✅ Basic CSS styling (colorful & user-friendly)
- ✅ Navigation between pages:
  - Login page
  - Main dashboard
  - Add document page
  - View documents page
  - Logout

## 📋 Additional Features

- 🔍 **Search & Filter**: Search documents by name/description and filter by category
- 📊 **Dashboard**: Statistics overview with recent documents
- 👥 **User Management**: Admin can manage categories
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile
- 🔒 **Security**: File type validation, size limits, and user permissions
- 🎨 **Modern UI**: Beautiful gradient design with smooth animations

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   # Place all files in your web server directory
   ```

2. **Create MySQL Database**
   ```bash
   # Import the database setup file
   mysql -u root -p < database/setup.sql
   ```

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Update database credentials:
     ```php
     $host = 'localhost';
     $dbname = 'worker_doc_system';
     $username = 'your_username';
     $password = 'your_password';
     ```

4. **Set File Permissions**
   ```bash
   # Create uploads directory with proper permissions
   mkdir uploads
   chmod 755 uploads
   ```

5. **Access the System**
   - Open your browser and navigate to the project URL
   - Default admin credentials:
     - **Username**: admin
     - **Password**: admin123

## 📁 File Structure

```
WORKERSYSTEM/
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── database/
│   └── setup.sql
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── session.php
├── uploads/          # Document storage
├── add_document.php
├── categories.php
├── dashboard.php
├── documents.php
├── download.php
├── edit_document.php
├── index.php
├── login.php
├── logout.php
└── README.md
```

## 🔐 Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **Session Management**: Secure session handling with automatic logout
- **File Validation**: Only allowed file types can be uploaded
- **Size Limits**: Maximum 10MB file size limit
- **User Permissions**: Users can only edit/delete their own documents
- **SQL Injection Protection**: All queries use prepared statements

## 📊 Database Schema

### Users Table
- `id` (Primary Key)
- `username` (Unique)
- `password` (Hashed)
- `email` (Unique)
- `full_name`
- `role` (admin/user)
- `created_at`

### Categories Table
- `id` (Primary Key)
- `name`
- `description`
- `created_at`

### Documents Table
- `id` (Primary Key)
- `name`
- `description`
- `filename`
- `file_path`
- `file_size`
- `file_type`
- `category_id` (Foreign Key)
- `uploaded_by` (Foreign Key)
- `created_at`
- `updated_at`

## 🎨 UI/UX Features

- **Modern Design**: Gradient backgrounds and smooth animations
- **Responsive Layout**: Works perfectly on all device sizes
- **Intuitive Navigation**: Clear menu structure and breadcrumbs
- **Visual Feedback**: Success/error messages and hover effects
- **Colorful Interface**: Engaging color scheme with proper contrast
- **User-Friendly**: Easy-to-use forms and clear action buttons

## 🔧 Customization

### Adding New File Types
Edit `add_document.php` and update the `$allowed_types` array:
```php
$allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'xlsx'];
```

### Changing File Size Limit
Update the size check in `add_document.php`:
```php
if ($file['size'] > 20 * 1024 * 1024) { // 20MB limit
```

### Modifying Colors
Edit `assets/css/style.css` and update the CSS variables or gradient values.

## 🚀 Usage Guide

### For Users
1. **Login** with your credentials
2. **View Documents** on the dashboard
3. **Upload Documents** using the "Add Document" page
4. **Search & Filter** documents by name or category
5. **Download** documents you need
6. **Edit** your own documents (name, description, category)

### For Admins
1. **Manage Categories** - Add/remove document categories
2. **View All Documents** - Access to all user documents
3. **Delete Any Document** - Full administrative control
4. **System Overview** - Dashboard with system statistics

## 📞 Support

This system is built to be simple, secure, and efficient. All code is well-commented and follows PHP best practices.

## 📝 License

This project is created for educational and business use. Feel free to modify and extend as needed.

---

**Built with ❤️ for efficient document management** 