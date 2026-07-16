<?php
require_once '../api/db.php';
require_once '../api/auth.php';

requireAdminLogin();

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $orderNumber = $_POST['order_number'] ?? '';
            $customerName = $_POST['customer_name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if ($orderNumber && $customerName) {
                $stmt = $db->getConnection()->prepare('INSERT INTO orders (order_number, customer_name, description) VALUES (:order_number, :customer_name, :description)');
                $stmt->bindValue(':order_number', $orderNumber, SQLITE3_TEXT);
                $stmt->bindValue(':customer_name', $customerName, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                if ($stmt->execute()) {
                    $message = 'Order created successfully';
                }
            }
        } elseif ($_POST['action'] === 'update_status') {
            $orderId = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            if ($orderId && $status) {
                $stmt = $db->getConnection()->prepare('UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $stmt->bindValue(':status', $status, SQLITE3_TEXT);
                $stmt->bindValue(':id', $orderId, SQLITE3_NUM);
                $stmt->execute();
                $message = 'Order updated successfully';
            }
        } elseif ($_POST['action'] === 'delete') {
            $orderId = $_POST['order_id'] ?? 0;
            if ($orderId) {
                $db->exec("DELETE FROM materials WHERE order_id = $orderId");
                $db->exec("DELETE FROM order_assignments WHERE order_id = $orderId");
                $db->exec("DELETE FROM orders WHERE id = $orderId");
                $message = 'Order deleted successfully';
            }
        } elseif ($_POST['action'] === 'assign_worker') {
            $orderId = $_POST['order_id'] ?? 0;
            $workerId = $_POST['worker_id'] ?? 0;
            if ($orderId && $workerId) {
                $stmt = $db->getConnection()->prepare('INSERT INTO order_assignments (order_id, worker_id) VALUES (:order_id, :worker_id)');
                $stmt->bindValue(':order_id', $orderId, SQLITE3_NUM);
                $stmt->bindValue(':worker_id', $workerId, SQLITE3_NUM);
                if ($stmt->execute()) {
                    $message = 'Worker assigned successfully';
                }
            }
        }
    }
}

// Get all orders
$result = $db->query('SELECT * FROM orders ORDER BY created_at DESC');
$orders = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $orders[] = $row;
}

// Get all workers
$workerResult = $db->query('SELECT * FROM workers WHERE status = "active" ORDER BY name');
$workers = [];
while ($row = $workerResult->fetchArray(SQLITE3_ASSOC)) {
    $workers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Wimi Elektro Admin</title>
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
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-section h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #764ba2;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .order-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .order-table tr:hover {
            background: #f9f9f9;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .status.in-progress {
            background: #cfe2ff;
            color: #084298;
        }
        .status.completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .order-actions button {
            padding: 8px 12px;
            font-size: 12px;
        }
        h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Wimi Elektro - Orders</h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="workers.php">Workers</a>
            <a href="materials.php">Materials</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Create New Order</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group">
                        <label for="order_number">Order Number</label>
                        <input type="text" id="order_number" name="order_number" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit">Create Order</button>
            </form>
        </div>

        <div class="form-section">
            <h2>All Orders</h2>
            <?php if ($orders): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($order['description'], 0, 50)); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in-progress" <?php echo $order['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="order-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="assign_worker">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="worker_id" required>
                                                <option value="">Assign Worker</option>
                                                <?php foreach ($workers as $worker): ?>
                                                    <option value="<?php echo $worker['id']; ?>"><?php echo htmlspecialchars($worker['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" style="padding: 5px 10px; font-size: 11px;">Assign</button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this order?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="danger" style="padding: 5px 10px; font-size: 11px;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders yet. Create one above.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
