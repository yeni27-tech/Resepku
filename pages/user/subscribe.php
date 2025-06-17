<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/auth/login.php');
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fungsi kirim email (konfigurasi SMTP di php.ini diperlukan)
function send_confirmation_email($email, $action)
{
    $subject = $action === 'subscribe' ? 'Konfirmasi Berlangganan Newsletter' : 'Konfirmasi Berhenti Berlangganan';
    $message = $action === 'subscribe' ?
        "Terima kasih telah berlangganan newsletter Resepku!\n\nSelamat menikmati resep-resep terbaru kami.\n\nTeam Resepku" :
        "Anda telah berhasil berhenti berlangganan newsletter Resepku.\n\nHubungi kami jika ada pertanyaan.\n\nTeam Resepku";
    $headers = "From: no-reply@resepku.com\r\n";
    return mail($email, $subject, $message, $headers);
}

// Proses subscribe/unsubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $action = $_POST['action'] ?? 'subscribe';
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    } else {
        try {
            if ($action === 'subscribe') {
                $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, email, subscribed_at) VALUES (?, ?, NOW()) 
                                       ON DUPLICATE KEY UPDATE subscribed_at = NOW()");
                $stmt->execute([$_SESSION['user_id'], $email]);
                send_confirmation_email($email, 'subscribe');
                $success = "Berlangganan berhasil! Cek email Anda untuk konfirmasi.";
            } else { // unsubscribe
                $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ? AND email = ?");
                $stmt->execute([$_SESSION['user_id'], $email]);
                if ($stmt->rowCount() > 0) {
                    send_confirmation_email($email, 'unsubscribe');
                    $success = "Berhenti berlangganan berhasil! Cek email Anda.";
                } else {
                    $errors[] = "Langganan tidak ditemukan.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Error memproses langganan: " . $e->getMessage();
            error_log("Error in subscribe.php: " . $e->getMessage());
        }
    }
}

// Ambil status langganan
$is_subscribed = false;
try {
    $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $is_subscribed = $stmt->fetchColumn() !== false;
} catch (PDOException $e) {
    error_log("Error checking subscription: " . $e->getMessage());
}

$pageTitle = "Berlangganan Newsletter - Resepku";
require_once '../../includes/header.php';
?>

<style>
    .subscribe-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background: #1f2937;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        color: #f5f5f5;
    }

    .subscribe-container h1 {
        font-size: 1.75rem;
        color: #ff6b35;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .subscribe-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .subscribe-form input[type="email"] {
        padding: 0.75rem;
        border: 1px solid #4b5563;
        border-radius: 8px;
        background: #2d3748;
        color: #f5f5f5;
        font-size: 1rem;
    }

    .btn-glow {
        background: #ff6b35;
        color: #f5f5f5;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: box-shadow 0.3s ease;
        width: fit-content;
        align-self: center;
    }

    .btn-glow:hover {
        box-shadow: 0 0 15px rgba(255, 107, 53, 0.7);
    }

    .unsubscribe-btn {
        background: #6b7280;
        color: #f5f5f5;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: box-shadow 0.3s ease;
        width: fit-content;
        align-self: center;
        margin-top: 1rem;
    }

    .unsubscribe-btn:hover {
        box-shadow: 0 0 15px rgba(107, 114, 128, 0.7);
    }

    .back-link {
        text-align: center;
        margin-top: 1rem;
    }

    .back-link a {
        color: #ff6b35;
        text-decoration: none;
    }

    .back-link a:hover {
        text-decoration: underline;
    }
</style>

<main class="main-content">
    <section class="container">
        <div class="subscribe-container">
            <h1>Berlangganan Newsletter</h1>
            <?php if (isset($success)): ?>
                <p class="text-green-500 mb-4" data-toast><?php echo htmlspecialchars($success); ?></p>
            <?php elseif (isset($error)): ?>
                <p class="text-red-500 mb-4" data-toast><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!$is_subscribed): ?>
                <form method="POST" class="subscribe-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="email" name="email" placeholder="Masukkan email Anda" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    <button type="submit" class="btn-glow">Berlangganan</button>
                </form>
            <?php else: ?>
                <p style="text-align: center;">Anda sudah berlangganan newsletter!</p>
                <form method="POST" class="subscribe-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="unsubscribe">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    <button type="submit" class="unsubscribe-btn">Berhenti Berlangganan</button>
                </form>
            <?php endif; ?>
            <div class="back-link">
                <p>Kembali ke <a href="/resep_website_native/">halaman utama</a>.</p>
            </div>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>