# Wimi Elektro - Order Management System (JSON Version)

A minimal viable product for managing orders, assigning workers, and tracking materials - completely file-based using JSON.

## ✨ Features
- ✅ Order management dashboard
- ✅ Worker assignment (up to 5 workers)
- ✅ Material/parts tracking per order
- ✅ Password-protected admin dashboard
- ✅ JSON-based persistent storage (no database needed)
- ✅ Worker access to assigned orders
- ✅ Zero dependencies - pure PHP & HTML/CSS

## Requirements
- PHP 7.0+
- Web server (Apache, Nginx, etc.)
- Write permissions for `/data` folder

## File Structure
```
wimi-elektro/
├── admin/                 # Admin dashboard
│   ├── login.php
│   ├── index.php          # Dashboard
│   ├── orders.php
│   ├── workers.php
│   ├── materials.php
│   └── logout.php
├── worker/               # Worker dashboard
│   ├── login.php
│   ├── index.php
│   └── logout.php
├── api/
│   ├── db.php            # JSON file operations
│   └── auth.php          # Session management
├── data/                 # JSON storage (auto-created)
│   ├── admin.json
│   ├── workers.json
│   ├── orders.json
│   ├── materials.json
│   └── order_assignments.json
└── README.md
```

## Data Storage

All data is stored as JSON files in the `/data` folder:

### admin.json
```json
[
  {
    "id": 1,
    "username": "admin",
    "password": "$2y$10$...",
    "created_at": "2024-01-01 10:00:00"
  }
]
```

### workers.json
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "status": "active",
    "created_at": "2024-01-01 10:00:00"
  }
]
```

### orders.json
```json
[
  {
    "id": 1,
    "order_number": "ORD-001",
    "customer_name": "ACME Corp",
    "description": "Custom electronics",
    "status": "pending",
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
]
```

### materials.json
```json
[
  {
    "id": 1,
    "order_id": 1,
    "material_name": "Copper Wire",
    "quantity": 10,
    "unit": "m",
    "purchased": false,
    "created_at": "2024-01-01 10:00:00"
  }
]
```

### order_assignments.json
```json
[
  {
    "id": 1,
    "order_id": 1,
    "worker_id": 1,
    "assigned_at": "2024-01-01 10:00:00"
  }
]
```

## Setup Instructions

### 1. Upload to Server
Upload all files to your hosting via FTP

### 2. Create Data Folder
```bash
mkdir data
chmod 755 data
```

### 3. Access the App
- **Admin Dashboard**: `https://admin.wimi-elektro.de/admin/login.php`
- **Worker Dashboard**: `https://wimi-elektro.de/worker/login.php`

### 4. Default Credentials
- Username: `admin`
- Password: `admin123`

**⚠️ Change the password after first login!**

## How It Works

### Admin Dashboard
1. Login with admin credentials
2. **Orders**: Create, update status, delete orders
3. **Workers**: Add up to 5 workers, deactivate workers
4. **Materials**: Add materials to orders, mark as purchased
5. **Assign**: Link workers to orders

### Worker Dashboard
1. Select your name to login (no password)
2. View all assigned orders
3. See order details and materials needed
4. Cannot edit - read-only access

## API Functions (db.php)

### readJSON($filename)
Reads and returns a JSON file as array
```php
$orders = readJSON('orders');
```

### writeJSON($filename, $data)
Writes/updates a JSON file
```php
writeJSON('orders', $orders);
```

### getNextId($filename)
Gets the next available ID for a resource
```php
$newId = getNextId('orders');
```

## Customization

### Change Admin Password
Edit `/data/admin.json` and update the password hash:
```php
$newPassword = password_hash('newpassword', PASSWORD_BCRYPT);
```

### Add More Workers
Manually edit `/data/workers.json` to add more workers

### Backup Data
Simply download the `/data` folder via FTP to backup all data

## Security Notes

✅ Passwords hashed with BCRYPT
✅ Session-based authentication
✅ No SQL injection (file-based)
✅ Input validation and sanitization
✅ Worker isolation (can only see assigned orders)

⚠️ Recommendations:
1. Change default admin password
2. Use HTTPS only
3. Protect `/data` folder (add .htaccess for Apache)
4. Regular backups

## Limitations

- File-based (slower than database for large datasets)
- Single server only (not suitable for distributed systems)
- No real-time synchronization
- Maximum ~1000 concurrent records recommended

## Advantages

✨ No database setup needed
✨ Simple JSON format (human readable)
✨ Easy backups (copy JSON files)
✨ Minimal server requirements
✨ Zero dependencies
✨ Works on any PHP hosting

## Support

For issues or questions, check the JSON files in `/data` folder to verify data integrity.
