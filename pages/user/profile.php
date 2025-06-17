<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/auth/login.php');
    exit;
}

// Ambil data user
$user = null;
try {
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at, profile_image, location, about 
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
    error_log("Error in profile.php: " . $e->getMessage());
}

// Ambil preferensi
$preferences = null;
try {
    $stmt = $pdo->prepare("SELECT preferred_categories, diet_type 
                           FROM preferences 
                           WHERE user_id = ? 
                           ORDER BY id DESC 
                           LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil preferensi: " . $e->getMessage();
    error_log("Error fetching preferences: " . $e->getMessage());
}

// Ambil resep user
$user_recipes = [];
try {
    $stmt = $pdo->prepare("SELECT r.id, r.title, r.image, c.name as category_name 
                           FROM recipes r 
                           LEFT JOIN categories c ON r.category_id = c.id 
                           WHERE r.user_id = ? 
                           ORDER BY r.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $user_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil resep: " . $e->getMessage();
    error_log("Error fetching user recipes: " . $e->getMessage());
}

$pageTitle = "Profil - Resepku";
require_once '../../includes/header.php';
?>

<style>
    .profile-header {
        position: relative;
        background: linear-gradient(135deg, #ffffff, #ffe4d6);
        padding: 3rem 1rem;
        border-radius: 15px;
        text-align: center;
        color: #333333;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .profile-header img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ff6b35;
        box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);
    }

    .profile-header h2 {
        font-size: 1.75rem;
        margin-top: 1rem;
        color: #ff6b35;
    }

    .edit-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #ff6b35;
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 50%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .edit-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(255, 107, 53, 0.7);
    }

    .info-card {
        background: #f9f9f9;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .info-card div {
        margin-bottom: 1rem;
        color: #333333;
    }

    .info-card label {
        display: block;
        font-weight: 600;
        color: #ff6b35;
        margin-bottom: 0.25rem;
    }

    .recipe-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .recipe-card {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 1rem;
        width: 100%;
        max-width: 300px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .recipe-card:hover {
        transform: translateY(-5px);
    }

    .recipe-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
    }

    .recipe-card h3 {
        font-size: 1.1rem;
        margin: 0.5rem 0;
        color: #333333;
    }

    .recipe-card a {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #ff6b35;
        color: #ffffff;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .recipe-card a:hover {
        background: #e65b2a;
    }

    .no-activity {
        text-align: center;
        margin: 2rem 0;
        color: #666666;
    }

    .no-activity img {
        width: 60px;
    }

    .no-activity a {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.75rem 1.5rem;
        background: #ff6b35;
        color: #ffffff;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .no-activity a:hover {
        background: #e65b2a;
    }
</style>

<main class="main-content">
    <section class="container" style="padding: 2rem 0; max-width: 1000px; margin: 0 auto;">
        <?php if (isset($error)): ?>
            <p class="error-message" style="background-color: #ffebee; color: #c62828; padding: 0.5rem; border-radius: 5px; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (!$user): ?>
            <p class="error-message" style="background-color: #ffebee; color: #c62828; padding: 0.5rem; border-radius: 5px; margin-bottom: 1rem; text-align: center;">Data user tidak ditemukan.</p>
        <?php else: ?>
            <!-- Header Profil -->
            <div class="profile-header">
                <a href="/resep_website_native/pages/user/edit_profile.php" class="edit-btn" title="Edit Profil">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                    </svg>
                </a>
                <img src="<?php echo !empty($user['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $user['profile_image']) ? '/resep_website_native/' . htmlspecialchars($user['profile_image']) : '/resep_website_native/assets/images/default-profile.png'; ?>" alt="Foto Profil">
                <h2><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></h2>
            </div>

            <!-- Info User -->
            <div class="info-card">
                <div>
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
                </div>
                <div>
                    <label>Lokasi</label>
                    <span><?php echo htmlspecialchars($user['location'] ?? 'Belum diatur'); ?></span>
                </div>
                <div>
                    <label>Tentang</label>
                    <span><?php echo htmlspecialchars($user['about'] ?? 'Belum ada deskripsi'); ?></span>
                </div>
                <!-- <div>
                    <label>Preferensi Kategori</label>
                    <span><?php echo htmlspecialchars($preferences['preferred_categories'] ?? 'Belum diatur'); ?></span>
                </div> -->
                <div>
                    <label>Tipe Diet</label>
                    <span><?php echo htmlspecialchars($preferences['diet_type'] ?? 'Belum diatur'); ?></span>
                </div>
            </div>

            <!-- Resep User -->
            <div style="margin-top: 2rem;">
                <h2 style="font-size: 1.5rem; margin-bottom: 1rem; color: #ff6b35;">Resep Saya</h2>
                <?php if (empty($user_recipes)): ?>
                    <div class="no-activity">
                        <img src="/resep_website_native/assets/images/default-resep.png" alt="Pot Icon">
                        <p>Belum ada aktivitas memasak!</p>
                        <a href="/resep_website_native/pages/user/manage_recipes.php">Tambah Resep</a>
                    </div>
                <?php else: ?>
                    <div class="recipe-cards">
                        <?php foreach ($user_recipes as $recipe): ?>
                            <div class="recipe-card">
                                <img src="<?php echo !empty($recipe['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $recipe['image']) ? '/resep_website_native/' . htmlspecialchars($recipe['image']) : '/resep_website_native/assets/images/default-resep.png'; ?>" alt="Foto Resep">
                                <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <a href="/resep_website_native/pages/recipes/detail.php?id=<?php echo $recipe['id']; ?>">Lihat Resep</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>