<?php
ob_start();
session_start();

// // Inisialisasi session tema jika belum ada
// if (!isset($_SESSION['theme'])) {
//     $_SESSION['theme'] = 'light';
// }

// Cek login status berdasarkan email atau user_id
$logged_in = isset($_SESSION['user_id']) || isset($_SESSION['email']);
if (!$logged_in && !in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])) {
    if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
        header('Location: ../login.php');
        exit;
    }
}

// // Toggle tema
// if (isset($_GET['theme'])) {
//     $_SESSION['theme'] = $_GET['theme'] === 'dark' ? 'dark' : 'light';
//     header('Location: ' . $_SERVER['HTTP_REFERER']);
//     exit;
// }

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Ambil data user dari database berdasarkan email atau user_id
if ($logged_in && empty($_SESSION['user'])) {
    require_once 'config.php';
    try {
        $identifier = $_SESSION['user_id'] ?? null;
        if (!$identifier && isset($_SESSION['email'])) {
            $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
        } else {
            $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
            $stmt->execute([$identifier]);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['email'] = $user['email'];
        } else {
            session_destroy();
            header('Location: ../login.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Session error: " . $e->getMessage());
    }
}

// Debug session
$user_id = $_SESSION['user_id'] ?? 'Not Set';
$email = $_SESSION['email'] ?? 'Not Set';
$role = $_SESSION['role'] ?? 'Not Set';
error_log("Session Debug - User ID: $user_id, Email: $email, Role: $role");

ob_end_flush();
