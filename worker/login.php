<?php
require_once '../api/db.php';
require_once '../api/auth.php';

startSecureSession();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerId = $_POST['worker_id'] ?? 0;

    if ($workerId) {
        $stmt = $db->getConnection()->prepare('SELECT * FROM workers WHERE id = :id AND status = :status');
        $stmt->bindValue(':id', $workerId, SQLITE3_NUM);
        $stmt->bindValue(':status', 'active', SQLITE3_TEXT);
        $result = $stmt->execute();
        $worker = $result->fetchArray(SQLITE3_ASSOC);

        if ($worker) {
            $_SESSION['worker_id'] = $worker['id'];
            $_SESSION['worker_name'] = $worker['name'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Worker not found or inactive';
        }
    } else {
        $error = 'Please select a worker';
    }
}

// Get all active workers
$result = $db->query('SELECT id, name FROM workers WHERE status = "active" ORDER BY name');
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
    <title>Wimi Elektro - Worker Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        h2 {
            text-align: center;
            color: #999;
            font-size: 14px;
            margin-bottom: 30px;
            font-weight: normal;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #764ba2;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .empty-message {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Wimi Elektro</h1>
        <h2>Worker Dashboard</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($workers): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="worker_id">Select Your Name</label>
                    <select id="worker_id" name="worker_id" required autofocus>
                        <option value="">-- Choose your name --</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?php echo $worker['id']; ?>"><?php echo htmlspecialchars($worker['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Login</button>
            </form>
        <?php else: ?>
            <div class="empty-message">
                <p>No active workers available.</p>
                <p style="font-size: 12px; margin-top: 10px; color: #999;">Please ask your admin to add workers.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
