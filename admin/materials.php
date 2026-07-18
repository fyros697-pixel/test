<?php
require_once '../api/db.php';
require_once '../api/auth.php';

requireAdminLogin();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $materials = readJSON('materials');
        
        if ($_POST['action'] === 'add') {
            $orderId = $_POST['order_id'] ?? 0;
            $materialName = $_POST['material_name'] ?? '';
            $quantity = $_POST['quantity'] ?? 1;
            $unit = $_POST['unit'] ?? 'pcs';
            
            if ($orderId && $materialName) {
                $materials[] = [
                    'id' => getNextId('materials'),
                    'order_id' => $orderId,
                    'material_name' => $materialName,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'purchased' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                writeJSON('materials', $materials);
                $message = 'Material added successfully';
            }
        } elseif ($_POST['action'] === 'mark_purchased') {
            $materialId = $_POST['material_id'] ?? 0;
            if ($materialId) {
                foreach ($materials as &$material) {
                    if ($material['id'] == $materialId) {
                        $material['purchased'] = true;
                    }
                }
                writeJSON('materials', $materials);
                $message = 'Material marked as purchased';
            }
        } elseif ($_POST['action'] === 'delete') {
            $materialId = $_POST['material_id'] ?? 0;
            if ($materialId) {
                $materials = array_filter($materials, fn($m) => $m['id'] != $materialId);
                writeJSON('materials', $materials);
                $message = 'Material deleted';
            }
        }
    }
}

// Get all orders and materials
$orders = readJSON('orders');
$materials = readJSON('materials');
$unpurchasedMaterials = array_filter($materials, fn($m) => $m['purchased'] === false);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials - Wimi Elektro Admin</title>
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
            max-width: 1000px;
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
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
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
        button.success {
            background: #28a745;
        }
        button.success:hover {
            background: #218838;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        .material-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .material-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .material-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .material-table tr:hover {
            background: #f9f9f9;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        .material-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .material-actions button {
            padding: 8px 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Wimi Elektro - Materials</h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="workers.php">Workers</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Add Material to Order</h2>
            <?php if ($orders): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_id">Order</label>
                            <select id="order_id" name="order_id" required>
                                <option value="">Select an order</option>
                                <?php foreach ($orders as $order): ?>
                                    <option value="<?php echo $order['id']; ?>"><?php echo htmlspecialchars($order['order_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="material_name">Material Name</label>
                            <input type="text" id="material_name" name="material_name" required>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select id="unit" name="unit">
                            <option value="pcs">pcs</option>
                            <option value="m">m</option>
                            <option value="kg">kg</option>
                            <option value="l">l</option>
                        </select>
                    </div>
                    <button type="submit">Add Material</button>
                </form>
            <?php else: ?>
                <p>No orders available. Create an order first.</p>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h2>Materials to Purchase</h2>
            <?php if ($unpurchasedMaterials): ?>
                <table class="material-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unpurchasedMaterials as $material): 
                            $order = array_filter($orders, fn($o) => $o['id'] == $material['order_id']);
                            $order = reset($order);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                                <td><?php echo $material['quantity'] . ' ' . htmlspecialchars($material['unit']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($material['created_at'])); ?></td>
                                <td>
                                    <div class="material-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="mark_purchased">
                                            <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                            <button type="submit" class="success">✓ Purchased</button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this material?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                            <button type="submit" class="danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No materials to purchase.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
