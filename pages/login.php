<?php
ob_start();
require_once '../includes/session.php';
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/user/profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $user['role'];
                header('Location: /resep_website_native/pages/user/profile.php');
                exit;
            } else {
                $error = "Email atau password salah.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan saat login.";
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $error = "Silakan isi email dan password.";
    }
}

$pageTitle = "Login - Resepku";
require_once '../includes/header.php';
?>

<style>
    .login-container {
        max-width: 400px;
        margin: 2rem auto;
        padding: 2rem;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .login-container h2 {
        font-size: 1.5rem;
        color: #ff6b35;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .login-form input {
        width: 100%;
        padding: 0.75rem;
        margin-bottom: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #ffffff;
        color: #333333;
        font-size: 1rem;
    }

    .login-form button {
        width: 100%;
        padding: 0.75rem;
        background: #ff6b35;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .login-form button:hover {
        background: #e65b2a;
    }

    .error-message {
        background-color: #ffebee;
        color: #c62828;
        padding: 0.5rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        text-align: center;
    }
</style>

<main class="main-content">
    <section class="container">
        <div class="login-container">
            <h2>Login Resepku</h2>
            <?php if ($error): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <input type="email" name="email" placeholder="Masukkan email" required>
                <input type="password" name="password" placeholder="Masukkan password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>
<?php ob_end_flush(); ?>