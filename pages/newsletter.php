<?php
require_once '../includes/session.php';
require_once '../includes/config.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/auth/login.php');
    exit;
}

// Ambil data user dari session
$user = $_SESSION['user'] ?? [];
if (empty($user) || !isset($user['email'])) {
    // Jika data user tidak lengkap, ambil dari database
    try {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user'] = $user; // Simpan ke session
        } else {
            header('Location: /resep_website_native/pages/auth/login.php');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = "Error mengambil data user: " . $e->getMessage();
        error_log("Error in newsletter.php: " . $e->getMessage());
    }
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil data kategori dan user untuk opsi
$categories = [];
$users = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query("SELECT id, username FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching categories/users: " . $e->getMessage();
    error_log("Error fetching categories/users: " . $e->getMessage());
}

// Fungsi kirim email dengan HTML
function send_confirmation_email($email, $action, $unsubscribe_token = null)
{
    $subject = $action === 'subscribe' ? 'Konfirmasi Berlangganan Newsletter' : 'Konfirmasi Berhenti Berlangganan';
    $base_url = 'http://localhost/resep_website_native/pages/newsletter.php';
    $unsubscribe_link = $unsubscribe_token ? "$base_url?unsubscribe={$unsubscribe_token}" : '';
    $message = $action === 'subscribe' ?
        "<html><body style='font-family: Arial, sans-serif; background-color: #ffffff; color: #333333; padding: 20px;'><h2 style='color: #ff6b35;'>Selamat Bergabung!</h2><p>Terima kasih telah berlangganan newsletter Resepku. Nikmati resep-resep terbaru kami!</p><p>Jika ingin berhenti berlangganan, klik <a href='{$unsubscribe_link}' style='color: #ff6b35; text-decoration: underline;'>di sini</a>.</p><p style='font-size: 12px; color: #666666;'>Team Resepku - " . date("h:i A, d M Y") . "</p></body></html>" :
        "<html><body style='font-family: Arial, sans-serif; background-color: #ffffff; color: #333333; padding: 20px;'><h2 style='color: #ff6b35;'>Terima Kasih!</h2><p>Anda telah berhenti berlangganan newsletter Resepku.</p><p style='font-size: 12px; color: #666666;'>Team Resepku - " . date("h:i A, d M Y") . "</p></body></html>";
    $headers = "From: no-reply@resepku.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($email, $subject, $message, $headers);
}

// Proses unsubscribe via link
if (isset($_GET['unsubscribe']) && !empty($_GET['unsubscribe'])) {
    $unsubscribe_token = $_GET['unsubscribe'];
    try {
        $stmt = $pdo->prepare("SELECT id, email FROM subscriptions WHERE unsubscribe_token = ? AND unsubscribed_at IS NULL");
        $stmt->execute([$unsubscribe_token]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($subscription) {
            $stmt = $pdo->prepare("UPDATE subscriptions SET unsubscribed_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$subscription['id']]);
            send_confirmation_email($subscription['email'], 'unsubscribe');
            $success = "Anda telah berhenti berlangganan. Cek email Anda.";
        } else {
            $errors[] = "Token unsubscribe tidak valid atau sudah digunakan.";
        }
    } catch (PDOException $e) {
        $errors[] = "Error memproses unsubscribe: " . $e->getMessage();
        error_log("Error in unsubscribe: " . $e->getMessage());
    }
}

// Proses subscribe/unsubscribe via form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $action = $_POST['action'] ?? 'subscribe';
    $subscription_type = $_POST['subscription_type'] ?? 'all';
    $category_id = $_POST['category_id'] ?? null;
    $creator_id = $_POST['creator_id'] ?? null;
    $errors = [];

    $email = $user['email'] ?? '';
    if (empty($email)) {
        $errors[] = "Email tidak tersedia. Silakan login ulang.";
    }

    if (empty($errors)) {
        try {
            if ($action === 'subscribe') {
                $unsubscribe_token = bin2hex(random_bytes(16));
                // Cek apakah kolom unsubscribe_token ada, jika tidak, hapus dari query
                $columns = $pdo->query("SHOW COLUMNS FROM subscriptions LIKE 'unsubscribe_token'")->fetch();
                $token_field = $columns ? 'unsubscribe_token' : '';
                $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, email, $token_field, subscription_type, category_id, creator_id) 
                                       VALUES (?, ?, " . ($token_field ? "?," : "") . " ?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE subscribed_at = CURRENT_TIMESTAMP, $token_field = VALUES($token_field), 
                                       subscription_type = VALUES(subscription_type), category_id = VALUES(category_id), creator_id = VALUES(creator_id)");
                $params = [$_SESSION['user_id'], $email];
                if ($token_field) $params[] = $unsubscribe_token;
                $params[] = $subscription_type;
                $params[] = $category_id;
                $params[] = $creator_id;
                $stmt->execute($params);
                send_confirmation_email($email, 'subscribe', $unsubscribe_token);
                $success = "Berhasil berlangganan newsletter! Cek email Anda.";
            } else {
                $stmt = $pdo->prepare("UPDATE subscriptions SET unsubscribed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND unsubscribed_at IS NULL");
                $stmt->execute([$_SESSION['user_id']]);
                if ($stmt->rowCount() > 0) {
                    send_confirmation_email($email, 'unsubscribe');
                    $success = "Berhasil berhenti berlangganan! Cek email Anda.";
                } else {
                    $errors[] = "Langganan tidak ditemukan atau sudah dihapus.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Error memproses langganan: " . $e->getMessage();
            error_log("Error in newsletter.php: " . $e->getMessage());
        }
    }
}

// Ambil status langganan
$is_subscribed = false;
$subscription_type = 'all';
try {
    $stmt = $pdo->prepare("SELECT subscription_type, category_id, creator_id FROM subscriptions WHERE user_id = ? AND unsubscribed_at IS NULL LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($subscription) {
        $is_subscribed = true;
        $subscription_type = $subscription['subscription_type'];
        $category_id = $subscription['category_id'];
        $creator_id = $subscription['creator_id'];
    }
} catch (PDOException $e) {
    error_log("Error checking subscription: " . $e->getMessage());
}

$pageTitle = "Newsletter - Resepku";
require_once '../includes/header.php';
?>

<style>
    .newsletter-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        color: #333333;
    }

    .newsletter-container h2 {
        font-size: 1.75rem;
        color: #ff6b35;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .newsletter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .newsletter-form input[type="email"] {
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #ffffff;
        color: #333333;
        font-size: 1rem;
        display: none;
    }

    .newsletter-form select {
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #ffffff;
        color: #333333;
        font-size: 1rem;
    }

    .btn-glow {
        background: #ff6b35;
        color: #ffffff;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 5px;
        transition: box-shadow 0.3s ease;
        width: fit-content;
        align-self: center;
        cursor: pointer;
    }

    .btn-glow:hover {
        box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
    }

    .unsubscribe-btn {
        background: #6b7280;
        color: #ffffff;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 5px;
        transition: box-shadow 0.3s ease;
        width: fit-content;
        align-self: center;
        margin-top: 1rem;
        cursor: pointer;
    }

    .unsubscribe-btn:hover {
        box-shadow: 0 0 10px rgba(107, 114, 128, 0.5);
    }

    .error-message,
    .success-message {
        padding: 0.5rem 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        text-align: center;
    }

    .error-message {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ef9a9a;
    }

    .success-message {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }

    .subscription-options {
        display: none;
    }

    .subscription-options.active {
        display: block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const subscriptionType = document.querySelector('select[name="subscription_type"]');
        const optionsDiv = document.querySelector('.subscription-options');

        function toggleOptions() {
            if (subscriptionType.value === 'category' || subscriptionType.value === 'creator') {
                optionsDiv.classList.add('active');
            } else {
                optionsDiv.classList.remove('active');
            }
        }

        subscriptionType.addEventListener('change', toggleOptions);
        toggleOptions();
    });
</script>

<main class="main-content">
    <section class="container">
        <div class="newsletter-container">
            <h2>Newsletter Resepku</h2>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (!empty($errors)): ?>
                <?php foreach ($errors as $err): ?>
                    <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            <?php elseif (isset($success)): ?>
                <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <?php if (!$is_subscribed): ?>
                <form method="POST" class="newsletter-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    <select name="subscription_type" required>
                        <option value="all">Semua Resep</option>
                        <option value="category">Kategori Favorit</option>
                        <option value="creator">Creator Favorit</option>
                    </select>
                    <div class="subscription-options">
                        <?php if (!empty($categories)): ?>
                            <select name="category_id">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <?php if (!empty($users)): ?>
                            <select name="creator_id">
                                <option value="">Pilih Creator</option>
                                <?php foreach ($users as $usr): ?>
                                    <option value="<?php echo htmlspecialchars($usr['id']); ?>">
                                        <?php echo htmlspecialchars($usr['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-glow">Berlangganan</button>
                </form>
            <?php else: ?>
                <p style="text-align: center;">Anda sudah berlangganan newsletter!</p>
                <p style="text-align: center; font-size: 0.9rem; color: #666666;">
                    Tipe langganan: <?php echo htmlspecialchars($subscription_type); ?>
                    <?php if ($subscription_type === 'category' && $category_id): ?>
                        (Kategori: <?php echo htmlspecialchars(array_column($categories, 'name', 'id')[$category_id] ?? 'N/A'); ?>)
                    <?php elseif ($subscription_type === 'creator' && $creator_id): ?>
                        (Creator: <?php echo htmlspecialchars(array_column($users, 'username', 'id')[$creator_id] ?? 'N/A'); ?>)
                    <?php endif; ?>
                </p>
                <form method="POST" class="newsletter-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="unsubscribe">
                    <button type="submit" class="unsubscribe-btn">Batalkan Langganan</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>