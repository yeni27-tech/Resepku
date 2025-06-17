<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/login.php');
    exit;
}

$success = '';
$error = '';
$existing_diet = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM preferences WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $existing_diet = '';
            $success = "Preferensi diet berhasil dihapus!";
        } catch (PDOException $e) {
            $error = "Error menghapus preferensi: " . $e->getMessage();
        }
    } else {
        $diet_type = trim($_POST['diet_type'] ?? '');
        if (!empty($diet_type)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO preferences (user_id, diet_type) VALUES (:user_id, :diet_type) 
                                      ON DUPLICATE KEY UPDATE diet_type = :diet_type, id = LAST_INSERT_ID(id)");
                $stmt->execute(['user_id' => $_SESSION['user_id'], 'diet_type' => $diet_type]);
                $stmt = $pdo->prepare("SELECT diet_type FROM preferences WHERE user_id = :user_id LIMIT 1");
                $stmt->execute(['user_id' => $_SESSION['user_id']]);
                $existing_diet = $stmt->fetchColumn() ?: '';
                $success = "Preferensi berhasil disimpan!";
            } catch (PDOException $e) {
                $error = "Error menyimpan preferensi: " . $e->getMessage();
            }
        } else {
            $error = "Pilih tipe diet terlebih dahulu!";
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT diet_type FROM preferences WHERE user_id = :user_id LIMIT 1");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $existing_diet = $stmt->fetchColumn() ?: '';
} catch (PDOException $e) {
    $error = "Error mengambil preferensi: " . $e->getMessage();
}
?>

<div class="container">
    <div class="preferences-form">
        <h1 class="title">Pengaturan Preferensi</h1>
        <div class="card">
            <form method="POST" class="form">
                <input type="hidden" name="action" value="">
                <div class="form-group">
                    <label class="label">Tipe Diet</label>
                    <select name="diet_type" class="select">
                        <option value="" <?php echo empty($existing_diet) ? 'selected' : ''; ?>>Pilih Tipe Diet</option>
                        <option value="vegan" <?php echo $existing_diet === 'vegan' ? 'selected' : ''; ?>>Vegan</option>
                        <option value="keto" <?php echo $existing_diet === 'keto' ? 'selected' : ''; ?>>Keto</option>
                        <option value="gluten-free" <?php echo $existing_diet === 'gluten-free' ? 'selected' : ''; ?>>Gluten-Free</option>
                        <option value="vegetarian" <?php echo $existing_diet === 'vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                        <option value="normal" <?php echo $existing_diet === 'normal' ? 'selected' : ''; ?>>Normal</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Preferensi</button>
                <?php if ($existing_diet): ?>
                    <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus preferensi diet?');">Hapus Preferensi Diet</button>
                <?php endif; ?>
            </form>
            <?php if ($success): ?>
                <p class="message success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="message error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #ffffff;
        --primary-dark: #f8f9fa;
        --primary-light: #ffffff;
        --secondary-color: #ff6b35;
        --secondary-light: #ff8c62;
        --secondary-dark: #e54e1b;
        --accent-color: #48bb78;
        --text-color: #2d3748;
        --text-light: #718096;
        --text-muted: #a0aec0;
        --bg-primary: #ffffff;
        --bg-secondary: #f7fafc;
        --bg-overlay: rgba(255, 255, 255, 0.95);
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: var(--bg-secondary);
    }

    .preferences-form {
        text-align: center;
    }

    .title {
        font-size: 2rem;
        font-weight: bold;
        color: var(--text-color);
        margin-bottom: 1.5rem;
    }

    .card {
        background: var(--primary-color);
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        border: 1px solid var(--text-muted);
        overflow: hidden;
    }

    .form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .label {
        display: block;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }

    .select {
        width: 100%;
        padding: 0.75rem;
        border-radius: 5px;
        border: 1px solid var(--text-muted);
        background: var(--primary-light);
        color: var(--text-color);
        transition: border-color 0.3s ease;
    }

    .select:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 5px rgba(72, 187, 120, 0.5);
    }

    .btn {
        padding: 0.75rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.1s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-primary {
        background-color: var(--secondary-color);
        color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--secondary-dark);
    }

    .btn-danger {
        background-color: #dc3545;
        color: var(--primary-color);
        margin-top: 1rem;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .message {
        margin-top: 1.5rem;
        padding: 0.75rem;
        border-radius: 5px;
        text-align: center;
        width: 100%;
        box-sizing: border-box;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 600px) {
        .card {
            padding: 1rem;
        }

        .btn {
            padding: 0.5rem;
        }
    }
</style>

<?php require_once '../../includes/footer.php'; ?>