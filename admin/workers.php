<?php
require_once '../api/db.php';
require_once '../api/auth.php';

requireAdminLogin();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            // Check worker limit
            $workerCount = $db->querySingle("SELECT COUNT(*) FROM workers WHERE status = 'active'");
            if ($workerCount >= 5) {
                $error = 'Maximum 5 workers allowed';
            } else {
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                
                if ($name) {
                    $stmt = $db->getConnection()->prepare('INSERT INTO workers (name, email) VALUES (:name, :email)');
                    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                    if ($stmt->execute()) {
                        $message = 'Worker created successfully';
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $workerId = $_POST['worker_id'] ?? 0;
            if ($workerId) {
                $db->exec("UPDATE workers SET status = 'inactive' WHERE id = $workerId");
                $message = 'Worker deactivated';
            }
        }
    }
}

// Get all active workers
$result = $db->query('SELECT * FROM workers WHERE status = "active" ORDER BY name');
$workers = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $workers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workers - Wimi Elektro Admin</title>
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
            max-width: 800px;
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
        input[type="email"] {
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
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        .workers-list {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .worker-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        .worker-item:last-child {
            border-bottom: none;
        }
        .worker-info h3 {
            color: #333;
            margin-bottom: 5px;
        }
        .worker-info p {
            color: #999;
            font-size: 12px;
        }
        .worker-count {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Wimi Elektro - Workers</h1>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="materials.php">Materials</a>
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
            <h2>Add New Worker</h2>
            <div class="worker-count"><?php echo count($workers); ?>/5 Workers</div>
            <?php if (count($workers) < 5): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="name">Worker Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email (optional)</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <button type="submit">Add Worker</button>
                </form>
            <?php else: ?>
                <p style="color: #666;">Maximum 5 workers reached</p>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h2>Active Workers</h2>
            <?php if ($workers): ?>
                <div class="workers-list">
                    <?php foreach ($workers as $worker): ?>
                        <div class="worker-item">
                            <div class="worker-info">
                                <h3><?php echo htmlspecialchars($worker['name']); ?></h3>
                                <p><?php echo htmlspecialchars($worker['email']); ?></p>
                                <p style="font-size: 11px;">Added: <?php echo date('d.m.Y', strtotime($worker['created_at'])); ?></p>
                            </div>
                            <form method="POST" onsubmit="return confirm('Deactivate this worker?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="worker_id" value="<?php echo $worker['id']; ?>">
                                <button type="submit" class="danger">Deactivate</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No active workers yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
