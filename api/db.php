<?php
// Get JSON data file path
function getDataPath($filename) {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir . '/' . $filename . '.json';
}

// Read JSON file
function readJSON($filename) {
    $path = getDataPath($filename);
    if (file_exists($path)) {
        $content = file_get_contents($path);
        return json_decode($content, true) ?? [];
    }
    return [];
}

// Write JSON file
function writeJSON($filename, $data) {
    $path = getDataPath($filename);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return true;
}

// Initialize admin if doesn't exist
function initializeAdmin() {
    $admins = readJSON('admin');
    if (empty($admins)) {
        $admins[] = [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        writeJSON('admin', $admins);
    }
}

// Get next ID for a resource
function getNextId($filename) {
    $data = readJSON($filename);
    if (empty($data)) {
        return 1;
    }
    $maxId = 0;
    foreach ($data as $item) {
        if (isset($item['id']) && $item['id'] > $maxId) {
            $maxId = $item['id'];
        }
    }
    return $maxId + 1;
}

// Initialize default data
initializeAdmin();
?>
