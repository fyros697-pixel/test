<?php
// Database initialization and connection

class Database {
    private $db;
    private $dbPath = '../data/wimi.db';

    public function __construct() {
        // Create data directory if it doesn't exist
        if (!is_dir(dirname($this->dbPath))) {
            mkdir(dirname($this->dbPath), 0755, true);
        }

        try {
            $this->db = new SQLite3($this->dbPath);
            $this->db->busyTimeout(5000);
            $this->initDatabase();
        } catch (Exception $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    private function initDatabase() {
        // Create tables if they don't exist
        $queries = [
            // Admin user table
            "CREATE TABLE IF NOT EXISTS admin (
                id INTEGER PRIMARY KEY,
                username TEXT UNIQUE,
                password TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Workers table
            "CREATE TABLE IF NOT EXISTS workers (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Orders table
            "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY,
                order_number TEXT UNIQUE,
                customer_name TEXT NOT NULL,
                description TEXT,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Order assignments (workers to orders)
            "CREATE TABLE IF NOT EXISTS order_assignments (
                id INTEGER PRIMARY KEY,
                order_id INTEGER NOT NULL,
                worker_id INTEGER NOT NULL,
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(order_id) REFERENCES orders(id),
                FOREIGN KEY(worker_id) REFERENCES workers(id)
            )",
            
            // Materials needed for orders
            "CREATE TABLE IF NOT EXISTS materials (
                id INTEGER PRIMARY KEY,
                order_id INTEGER NOT NULL,
                material_name TEXT NOT NULL,
                quantity INTEGER DEFAULT 1,
                unit TEXT DEFAULT 'pcs',
                purchased INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(order_id) REFERENCES orders(id)
            )"
        ];

        foreach ($queries as $query) {
            $this->db->exec($query);
        }

        // Create default admin if none exists
        $result = $this->db->querySingle("SELECT COUNT(*) FROM admin");
        if ($result == 0) {
            $password = password_hash('admin123', PASSWORD_BCRYPT);
            $this->db->exec("INSERT INTO admin (username, password) VALUES ('admin', '$password')");
        }
    }

    public function getConnection() {
        return $this->db;
    }

    public function query($sql) {
        return $this->db->query($sql);
    }

    public function querySingle($sql) {
        return $this->db->querySingle($sql);
    }

    public function exec($sql) {
        return $this->db->exec($sql);
    }

    public function close() {
        $this->db->close();
    }
}

$db = new Database();
?>
