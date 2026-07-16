<?php
require_once '../api/db.php';
require_once '../api/auth.php';

requireAdminLogin();

// Get dashboard stats
$totalOrders = $db->querySingle("SELECT COUNT(*) FROM orders");
$pendingOrders = $db->querySingle("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$totalWorkers = $db->querySingle("SELECT COUNT(*) FROM workers");
$totalMaterials = $db->querySingle("SELECT COUNT(*) FROM materials WHERE purchased = 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wimi Elektro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            font-size: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #999;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .nav-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .nav-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .nav-card h2 {
            margin-bottom: 10px;
            color: #667eea;
        }
        .nav-card p {
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Wimi Elektro - Admin</h1>
        <div>
            <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2 style="margin-bottom: 20px;">Dashboard</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $totalOrders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="number"><?php echo $pendingOrders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Workers</h3>
                <div class="number"><?php echo $totalWorkers; ?></div>
            </div>
            <div class="stat-card">
                <h3>Materials to Buy</h3>
                <div class="number"><?php echo $totalMaterials; ?></div>
            </div>
        </div>

        <h2 style="margin-bottom: 20px;">Management</h2>
        <div class="nav-links">
            <a href="orders.php" class="nav-card">
                <h2>📋 Orders</h2>
                <p>Create and manage orders</p>
            </a>
            <a href="workers.php" class="nav-card">
                <h2>👷 Workers</h2>
                <p>Manage workers (max 5)</p>
            </a>
            <a href="materials.php" class="nav-card">
                <h2>📦 Materials</h2>
                <p>Track order materials</p>
            </a>
        </div>
    </div>
</body>
</html>
