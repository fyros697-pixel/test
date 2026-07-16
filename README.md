# Wimi Elektro - Order Management System

A minimal viable product for managing orders, assigning workers, and tracking materials.

## Features
- Order management dashboard
- Worker assignment (up to 5 workers)
- Material/parts tracking per order
- Password-protected admin dashboard
- SQLite database for persistent storage
- Worker access to assigned orders

## Setup

### Requirements
- PHP 7.0+
- SQLite3 extension enabled

### Deployment
1. Upload all files to your FTP server
2. Admin dashboard: `admin.wimi-elektro.de`
3. Worker dashboard: `wimi-elektro.de/worker`
4. Database file created automatically on first run

## Directory Structure
```
├── admin/                 # Admin dashboard
│   ├── index.php
│   ├── orders.php
│   ├── workers.php
│   └── materials.php
├── worker/               # Worker dashboard
│   └── index.php
├── api/                  # API endpoints
│   ├── db.php
│   └── auth.php
└── data/                 # SQLite database
    └── wimi.db
```
