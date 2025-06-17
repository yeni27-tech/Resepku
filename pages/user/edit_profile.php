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

// Ambil data user
$user = null;
try {
    $stmt = $pdo->prepare("SELECT id, username, email, profile_image, location, about 
                           FROM users 
                           WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: /resep_website_native/pages/auth/login.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error mengambil data user: " . $e->getMessage();
    error_log("Error in edit_profile.php: " . $e->getMessage());
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $password = $_POST['password'] ?? '';
    $profile_image = $_FILES['profile_image'] ?? null;

    $errors = [];
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Nama harus 3-50 karakter.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if ($password && strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if ($profile_image && $profile_image['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (!in_array($profile_image['type'], $allowed_types)) {
            $errors[] = "Foto profil harus JPG atau PNG.";
        }
        if ($profile_image['size'] > $max_size) {
            $errors[] = "Foto profil maksimal 2MB.";
        }
    }

    // Cek username/email unik
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Nama sudah digunakan.";
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah digunakan.";
        }
    } catch (PDOException $e) {
        $errors[] = "Error memeriksa data: " . $e->getMessage();
    }

    if (empty($errors)) {
        try {
            $update_query = "UPDATE users SET username = ?, email = ?, location = ?, about = ?";
            $params = [$username, $email, $location, $about];
            if ($password) {
                $update_query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_BCRYPT);
            }
            $update_query .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];

            // Handle foto profil
            if ($profile_image && $profile_image['size'] > 0) {
                $ext = pathinfo($profile_image['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/assets/images/profiles/' . $filename;
                if (move_uploaded_file($profile_image['tmp_name'], $upload_path)) {
                    if ($user['profile_image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $user['profile_image'])) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $user['profile_image']);
                    }
                    $update_query = str_replace(" WHERE id = ?", ", profile_image = ? WHERE id = ?", $update_query);
                    $params[array_key_last($params)] = 'assets/images/profiles/' . $filename;
                    $params[] = $_SESSION['user_id'];
                } else {
                    $errors[] = "Gagal mengunggah foto profil.";
                }
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare($update_query);
                $stmt->execute($params);
                $success = "Profil berhasil diperbarui.";
                $_SESSION['user'] = array_merge($_SESSION['user'], ['username' => $username, 'email' => $email]); // Update session
                header('Location: profile.php?success=' . urlencode($success));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Error memperbarui profil: " . $e->getMessage();
            error_log("Error updating profile: " . $e->getMessage());
        }
    }
}

$pageTitle = "Edit Profil - Resepku";
require_once '../../includes/header.php';
?>

<style>
    .edit-container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .edit-container h2 {
        font-size: 1.75rem;
        color: #ff6b35;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #ff6b35;
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #ffffff;
        color: #333333;
        font-size: 1rem;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-group input[type="file"] {
        padding: 0;
    }

    #image-preview {
        max-width: 120px;
        margin-top: 0.5rem;
        border-radius: 50%;
        display: none;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .btn-glow {
        background: #ff6b35;
        color: #ffffff;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-glow:hover {
        background: #e65b2a;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #ff6b35;
        color: #ff6b35;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn-outline:hover {
        background: #ff6b35;
        color: #ffffff;
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
        <div class="edit-container">
            <h2>Edit Profil</h2>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (!empty($errors)): ?>
                <?php foreach ($errors as $err): ?>
                    <p class="error-message"><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            <?php elseif (isset($_GET['success'])): ?>
                <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="username">Nama</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Lokasi</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="about">Tentang</label>
                    <textarea id="about" name="about"><?php echo htmlspecialchars($user['about'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="profile_image">Foto Profil</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png">
                    <img id="image-preview" src="<?php echo !empty($user['profile_image']) ? '/resep_website_native/' . htmlspecialchars($user['profile_image']) : ''; ?>" alt="Preview" style="max-width: 120px; margin-top: 0.5rem; border-radius: 50%; display: <?php echo $user['profile_image'] ? 'block' : 'none'; ?>;">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-glow">Simpan</button>
                    <a href="/resep_website_native/pages/user/profile.php" class="btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const imageInput = document.getElementById('profile_image');
        const imagePreview = document.getElementById('image-preview');
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', () => {
                const file = imageInput.files[0];
                if (file) {
                    imagePreview.src = URL.createObjectURL(file);
                    imagePreview.style.display = 'block';
                }
            });
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>