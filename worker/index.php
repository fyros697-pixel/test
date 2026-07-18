<?php
require_once '../api/db.php';
require_once '../api/auth.php';

requireWorkerLogin();

$workerId = $_SESSION['worker_id'];
$workerName = $_SESSION['worker_name'];

// Get assigned orders for this worker
$assignments = readJSON('order_assignments');
$orders = readJSON('orders');
$materials = readJSON('materials');

$workerOrders = [];
foreach ($assignments as $assignment) {
    if ($assignment['worker_id'] == $workerId) {
        foreach ($orders as $order) {
            if ($order['id'] == $assignment['order_id']) {
                $workerOrders[] = $order;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Wimi Elektro</title>
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
            background: #667eea;
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
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .greeting {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .greeting h2 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .greeting p {
            color: #666;
            font-size: 14px;
        }
        .orders-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .orders-section h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .order-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .order-card:hover {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .status.inprogress {
            background: #cfe2ff;
            color: #084298;
        }
        .status.completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        .order-details {
            margin-bottom: 15px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .materials-section {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }
        .materials-section h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .material-item {
            background: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
        }
        .material-info {
            color: #333;
        }
        .material-qty {
            color: #999;
        }
        .empty-message {
            background: #f0f0f0;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px;
            color: #666;
        }
        .empty-message p {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Wimi Elektro</h1>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <div class="greeting">
            <h2>Welcome, <?php echo htmlspecialchars($workerName); ?></h2>
            <p>Your assigned orders are listed below</p>
        </div>

        <div class="orders-section">
            <h2>📋 My Assigned Orders</h2>
            
            <?php if ($workerOrders): ?>
                <?php foreach ($workerOrders as $order): ?>
                    <?php 
                    // Get materials for this order
                    $orderMaterials = array_filter($materials, fn($m) => $m['order_id'] == $order['id'] && $m['purchased'] === false);
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            <span class="status <?php echo str_replace('-', '', $order['status']); ?>">
                                <?php echo ucfirst(str_replace('-', ' ', $order['status'])); ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <div class="detail-label">Customer:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Description:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['description']); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Created:</div>
                                <div class="detail-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                        </div>

                        <?php if ($orderMaterials): ?>
                            <div class="materials-section">
                                <h4>📦 Materials Needed:</h4>
                                <?php foreach ($orderMaterials as $material): ?>
                                    <div class="material-item">
                                        <div class="material-info"><?php echo htmlspecialchars($material['material_name']); ?></div>
                                        <div class="material-qty"><?php echo $material['quantity'] . ' ' . htmlspecialchars($material['unit']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">
                    <p>No orders assigned to you yet.</p>
                    <p style="font-size: 12px; margin-top: 10px; color: #999;">Check back later for new assignments.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
