<?php
// Authentication helper

function startSecureSession() {
    session_start();
    // Prevent session fixation
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
    }
}

function requireAdminLogin() {
    startSecureSession();
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: /admin/login.php');
        exit();
    }
}

function requireWorkerLogin() {
    startSecureSession();
    if (!isset($_SESSION['worker_id'])) {
        header('Location: /worker/login.php');
        exit();
    }
}

function adminLogout() {
    session_start();
    session_destroy();
    header('Location: /admin/login.php');
    exit();
}

function workerLogout() {
    session_start();
    session_destroy();
    header('Location: /worker/login.php');
    exit();
}
?>
