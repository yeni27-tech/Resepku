<?php
require_once '../includes/session.php';
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/profile.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    } else {
        try {
            // Cek apakah username atau email sudah digunakan
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                if ($existingUser['username'] === $username) {
                    $error = "Username sudah digunakan.";
                } elseif ($existingUser['email'] === $email) {
                    $error = "Email sudah terdaftar.";
                }
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash]);
                $success = "Registrasi berhasil. Silakan login.";
                // Redirect ke login setelah registrasi
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = "Registrasi gagal: " . $e->getMessage();
            error_log("Register error: " . $e->getMessage());
        }
    }
}

$pageTitle = "Register - Resepku";
require_once '../includes/header.php';
?>

<style>
    .register-container {
        max-width: 400px;
        margin: 2rem auto;
        padding: 2rem;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .register-container h1 {
        font-size: 1.75rem;
        color: #ff6b35;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .register-form div {
        margin-bottom: 1rem;
    }

    .register-form label {
        display: block;
        font-size: 0.875rem;
        color: #333333;
        margin-bottom: 0.25rem;
    }

    .register-form input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #ffffff;
        color: #333333;
        font-size: 1rem;
    }

    .register-form button {
        width: 100%;
        padding: 0.75rem;
        background: #ff6b35;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .register-form button:hover {
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

    .success-message {
        background-color: #e8f5e9;
        color: #2e7d32;
        padding: 0.5rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        text-align: center;
    }
</style>

<main class="main-content">
    <section class="container">
        <div class="register-container">
            <h1>Register</h1>

            <?php if ($error): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif ($success): ?>
                <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <form method="POST" class="register-form">
                <div>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>