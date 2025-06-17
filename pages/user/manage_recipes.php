<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/user/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses form tambah resep
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $diet_type = trim($_POST['diet_type'] ?? '');
    $cooking_time = (int)($_POST['cooking_time'] ?? 0);
    $calories = (int)($_POST['calories'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Validasi
    $errors = [];
    if (strlen($title) < 3 || strlen($title) > 255) {
        $errors[] = "Judul harus 3-255 karakter.";
    }
    if (strlen($description) > 1000) {
        $errors[] = "Deskripsi maksimal 1000 karakter.";
    }
    if (empty($ingredients)) {
        $errors[] = "Bahan wajib diisi.";
    }
    if (empty($instructions)) {
        $errors[] = "Langkah wajib diisi.";
    }
    if ($category_id <= 0) {
        $errors[] = "Kategori wajib dipilih.";
    }
    if ($cooking_time < 0) {
        $errors[] = "Waktu masak harus positif.";
    }
    if ($calories < 0) {
        $errors[] = "Kalori harus positif.";
    }

    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Gambar harus JPG atau PNG.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "Gambar maksimal 2MB.";
        } else {
            $image_path = 'assets/images/recipes/' . uniqid() . '_' . basename($file['name']);
            if (!move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $image_path)) {
                $errors[] = "Gagal mengunggah gambar.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO recipes (user_id, title, description, ingredients, instructions, category_id, diet_type, cooking_time, calories, is_published, image) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $ingredients, $instructions, $category_id, $diet_type, $cooking_time, $calories, $is_published, $image_path]);
            $success = "Resep berhasil ditambahkan!";
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
            error_log("Error in manage_recipes.php: " . $e->getMessage());
        }
    }
}

// Ambil daftar resep
try {
    $stmt = $pdo->prepare("SELECT r.*, c.name as category_name 
                           FROM recipes r 
                           LEFT JOIN categories c ON r.category_id = c.id 
                           WHERE r.user_id = ? 
                           ORDER BY r.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error mengambil resep: " . $e->getMessage();
    error_log("Error in manage_recipes.php: " . $e->getMessage());
}

$pageTitle = "Kelola Resep - Resepku";
require_once '../../includes/header.php';
?>


<main class="main-content">
    <section class="container" style="padding: 2rem 0;">
        <div class="section-header">
            <h2>Resep Saya</h2>
        </div>
        <?php if (isset($success)): ?>
            <p class="error-message" style="background-color: #e6ffed; color: #2e7d32; border-color: #a5d6a7;" data-toast><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error-message" data-toast><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <p class="error-message" data-toast><?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Form Tambah Resep -->
        <div class="recipe-form-container" style="background-color: var(--white); padding: 1.5rem; border-radius: var(--border-radius-lg); box-shadow: var(--box-shadow); margin-bottom: 2rem;">
            <div class="form-header" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleForm()">
                <h3 style="font-size: var(--text-xl); font-weight: 600; color: var(--secondary-color);">Tambah Resep</h3>
                <i id="form-toggle-icon" class="fas fa-chevron-down" style="font-size: var(--text-lg); color: var(--primary-color);"></i>
            </div>
            <form id="recipe-form" action="" method="POST" enctype="multipart/form-data" class="recipe-form" style="margin-top: 1rem; display: none;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div style="margin-bottom: 1.5rem;">
                    <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Judul Resep</label>
                    <input type="text" id="title" name="title" value="" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="category_id" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kategori</label>
                    <select id="category_id" name="category_id" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                        <option value="">Pilih Kategori</option>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM categories");
                            while ($category = $stmt->fetch()) {
                                echo "<option value='{$category['id']}'>" . htmlspecialchars($category['name']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            error_log("Error fetching categories: " . $e->getMessage());
                        }
                        ?>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="diet_type" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Tipe Diet</label>
                    <select id="diet_type" name="diet_type"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                        <option value="">Semua</option>
                        <option value="vegan">Vegan</option>
                        <option value="keto">Keto</option>
                        <option value="gluten-free">Gluten-Free</option>
                        <option value="vegetarian">Vegetarian</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Deskripsi</label>
                    <textarea id="description" name="description" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 100px;"></textarea>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="ingredients" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Bahan-bahan</label>
                    <textarea id="ingredients" name="ingredients" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 150px;"></textarea>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="instructions" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Langkah-langkah</label>
                    <textarea id="instructions" name="instructions" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 150px;"></textarea>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="cooking_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Waktu Masak (menit)</label>
                    <input type="number" id="cooking_time" name="cooking_time" value="0" min="0"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="calories" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kalori (kcal)</label>
                    <input type="number" id="calories" name="calories" value="0" min="0"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="image" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Gambar Resep (JPG/PNG, max 2MB)</label>
                    <input type="file" id="image-input" name="image" accept="image/jpeg,image/png"
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
                    <img id="image-preview" src="/resep_website_native/assets/images/default-recipe.jpg"
                        alt="Preview Gambar" style="max-width: 200px; margin-top: 1rem; border-radius: var(--border-radius); display: none;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; font-weight: 600;">
                        <input type="checkbox" name="is_published" style="margin-right: 0.5rem;">
                        Publikasikan Resep
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Tambah Resep</button>
            </form>
        </div>

        <!-- Daftar Resep -->
        <div class="recipes-grid">
            <?php if (empty($recipes)): ?>
                <p class="no-recipes">Belum ada resep yang dibuat.</p>
            <?php else: ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-image" style="background-image: url('<?php echo $recipe['image'] ? '/resep_website_native/' . htmlspecialchars($recipe['image']) : '/resep_website_native/assets/images/default-recipe.png'; ?>');">
                            <div class="recipe-badges">
                                <span class="time-badge"><i class="fas fa-clock"></i> <?php echo $recipe['cooking_time'] ? htmlspecialchars($recipe['cooking_time']) . ' menit' : 'N/A'; ?></span>
                            </div>
                        </div>
                        <div class="recipe-info">
                            <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 80)); ?>...</p>
                            <div class="recipe-meta">
                                <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($recipe['category_name'] ?: 'N/A'); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $recipe['is_published'] ? 'Dipublikasikan' : 'Draft'; ?></span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="/resep_website_native/pages/recipes/edit.php?id=<?php echo $recipe['id']; ?>"
                                    class="btn btn-outline" style="flex: 1;">Edit</a>
                                <form action="/resep_website_native/pages/recipes/delete.php" method="POST"
                                    onsubmit="confirmDelete(event, this)" style="flex: 1;">
                                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="btn btn-outline"
                                        style="width: 100%; border-color: #d32f2f; color: #d32f2f;">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    function toggleForm() {
        const form = document.getElementById('recipe-form');
        const icon = document.getElementById('form-toggle-icon');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            form.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>

<style>
    /* 
   Resepku - Main Stylesheet
   Modern and clean design for recipe website
*/

    /* ===== FONT IMPORTS ===== */
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap');
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    /* ===== VARIABLES ===== */
    :root {
        /* Colors from index.css */
        --primary-color: #ff6b35;
        --primary-light: #ff8c62;
        --primary-dark: #e54e1b;
        --secondary-color: #2c3e50;
        --accent-color: #48bb78;
        --text-color: #333333;
        --text-light: #777777;
        --light-bg: #f8f9fa;
        --white: #ffffff;
        --border-color: #eaeaea;
        --shadow-color: rgba(0, 0, 0, 0.08);

        /* Colors for dark mode */
        --bg-primary: #1f2937;
        --bg-secondary: #111827;
        --text-primary: #f3f4f6;
        --text-secondary: #d1d5db;
        --accent: #f59e0b;

        /* Font sizes */
        --text-xs: 0.75rem;
        --text-sm: 0.875rem;
        --text-md: 1rem;
        --text-lg: 1.125rem;
        --text-xl: 1.25rem;
        --text-2xl: 1.5rem;
        --text-3xl: 1.875rem;
        --text-4xl: 2.25rem;

        /* Sizes */
        --container-width: 1200px;
        --header-height: 70px;
        --border-radius-sm: 4px;
        --border-radius: 8px;
        --border-radius-lg: 12px;
        --box-shadow: 0 4px 12px var(--shadow-color);
        --box-shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.15);

        /* Transitions */
        --transition-fast: 0.2s;
        --transition-normal: 0.3s;
        --transition-slow: 0.5s;
    }

    /* ===== RESET & BASE STYLES ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Nunito', sans-serif;
        color: var(--text-color);
        line-height: 1.6;
        background-color: var(--light-bg);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        font-size: var(--text-md);
    }

    body.dark {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
    }

    body.dark h1,
    body.dark h2,
    body.dark h3,
    body.dark h4,
    body.dark h5,
    body.dark h6 {
        color: var(--text-primary);
    }

    body.dark .recipe-card,
    body.dark .category-card,
    body.dark .quick-link,
    body.dark .recipe-form-container {
        background-color: var(--bg-primary);
    }

    body.dark .recipe-info h3,
    body.dark .recipe-meta span,
    body.dark .recipe-description {
        color: var(--text-primary);
    }

    body.dark .recipe-meta span,
    body.dark .recipe-description {
        color: var(--text-secondary);
    }

    body.dark .view-recipe,
    body.dark .newsletter-form button,
    body.dark .recipe-form button {
        background-color: var(--accent);
    }

    body.dark .view-recipe:hover,
    body.dark .newsletter-form button:hover,
    body.dark .recipe-form button:hover {
        background-color: #d97706;
    }

    body.dark .newsletter-section {
        background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url('/resep_website_native/assets/images/newsletter-bg.jpg');
    }

    body.dark .quick-links,
    body.dark .featured-recipes {
        background-color: var(--bg-primary);
    }

    a {
        text-decoration: none;
        color: var(--primary-color);
        transition: color var(--transition-normal);
    }

    a:hover {
        color: var(--primary-dark);
    }

    body.dark a {
        color: var(--accent);
    }

    body.dark a:hover {
        color: #d97706;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--secondary-color);
        line-height: 1.3;
    }

    img {
        max-width: 100%;
        height: auto;
    }

    button,
    input[type="submit"] {
        cursor: pointer;
        border: none;
        font-family: 'Nunito', sans-serif;
        font-weight: 600;
    }

    /* ===== LAYOUT ===== */
    .container {
        width: 100%;
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 0 1.25rem;
    }

    .main-content {
        flex: 1;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @keyframes loading {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .fadeIn {
        animation: fadeIn 0.8s ease forwards;
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    /* ===== HEADER ===== */
    .header {
        background-color: var(--white);
        box-shadow: 0 2px 4px var(--shadow-color);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    body.dark .header {
        background-color: var(--bg-primary);
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: var(--header-height);
    }

    .logo a {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--primary-color);
        display: flex;
        align-items: center;
    }

    body.dark .logo a {
        color: var(--accent);
    }

    .logo a i {
        margin-right: 0.5rem;
    }

    .main-nav ul {
        display: flex;
        list-style: none;
    }

    .main-nav li {
        margin-left: 1.5rem;
    }

    .main-nav a {
        color: var(--secondary-color);
        font-weight: 600;
        padding: 0.5rem 0;
        position: relative;
    }

    body.dark .main-nav a {
        color: var(--text-primary);
    }

    .main-nav a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 3px;
        bottom: 0;
        left: 0;
        background-color: var(--primary-color);
        transition: width var(--transition-normal);
        border-radius: 3px;
    }

    body.dark .main-nav a::after {
        background-color: var(--accent);
    }

    .main-nav a:hover::after,
    .main-nav a.active::after {
        width: 100%;
    }

    .mobile-nav-toggle {
        display: none;
        font-size: 1.5rem;
        background: none;
        border: none;
        color: var(--secondary-color);
    }

    body.dark .mobile-nav-toggle {
        color: var(--text-primary);
    }

    .user-actions {
        display: flex;
        align-items: center;
    }

    .user-actions a {
        margin-left: 1rem;
        transition: all var(--transition-normal);
    }

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: all var(--transition-normal);
        text-align: center;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: var(--white);
    }

    body.dark .btn-primary {
        background-color: var(--accent);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    body.dark .btn-primary:hover {
        background-color: #d97706;
    }

    .btn-outline {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        background-color: transparent;
    }

    body.dark .btn-outline {
        border-color: var(--accent);
        color: var(--accent);
    }

    .btn-outline:hover {
        background-color: var(--primary-color);
        color: var(--white);
        transform: translateY(-2px);
    }

    body.dark .btn-outline:hover {
        background-color: var(--accent);
    }

    /* ===== HERO SECTION ===== */
    .hero {
        background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('/resep_website_native/assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        color: var(--white);
        padding: 6rem 0;
        text-align: center;
        position: relative;
    }

    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 1.25rem;
        animation: fadeIn 1s ease-out;
    }

    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1.25rem;
        color: var(--white);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero .tagline {
        font-size: 1.25rem;
        margin-bottom: 2.5rem;
        color: #f0f0f0;
    }

    .search-container form {
        display: flex;
        max-width: 600px;
        margin: 0 auto;
    }

    .search-container select {
        padding: 1rem;
        border: none;
        border-radius: 4px 0 0 4px;
        font-size: 1rem;
        outline: none;
        background-color: var(--white);
        color: var(--text-color);
    }

    body.dark .search-container select {
        background-color: var(--bg-primary);
        color: var(--text-primary);
    }

    .search-container input {
        flex: 1;
        padding: 1rem 1.25rem;
        border: none;
        font-size: 1rem;
        outline: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: all var(--transition-normal);
    }

    .search-container input:focus {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
    }

    .search-container button {
        background-color: var(--primary-color);
        color: white;
        border-radius: 0 50px 50px 0;
        padding: 0.65rem 1.25rem;
        font-size: 1rem;
        transition: background-color var(--transition-normal);
    }

    body.dark .search-container button {
        background-color: var(--accent);
    }

    .search-container button:hover {
        background-color: var(--primary-dark);
    }

    body.dark .search-container button:hover {
        background-color: #d97706;
    }

    /* ===== QUICK LINKS ===== */
    .quick-links {
        background-color: var(--white);
        padding: 1.25rem 0;
        box-shadow: 0 4px 6px var(--shadow-color);
    }

    .quick-links .container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 1.25rem;
    }

    .quick-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        transition: all var(--transition-normal);
        color: var(--secondary-color);
    }

    body.dark .quick-link {
        color: var(--text-primary);
    }

    .quick-link:hover {
        background-color: var(--light-bg);
        transform: translateY(-4px);
        color: var(--primary-color);
        box-shadow: var(--box-shadow);
    }

    body.dark .quick-link:hover {
        background-color: var(--bg-primary);
        color: var(--accent);
    }

    .quick-link i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .quick-link span {
        font-weight: 600;
    }

    /* ===== SECTION HEADER ===== */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .section-header h2 {
        font-size: 1.875rem;
        position: relative;
        padding-left: 1rem;
    }

    .section-header h2::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 70%;
        width: 4px;
        background-color: var(--primary-color);
        border-radius: 2px;
    }

    body.dark .section-header h2::before {
        background-color: var(--accent);
    }

    .view-all {
        color: var(--primary-color);
        font-weight: 600;
        display: flex;
        align-items: center;
        transition: all var(--transition-normal);
    }

    body.dark .view-all {
        color: var(--accent);
    }

    .view-all:hover {
        color: var(--primary-dark);
        transform: translateX(4px);
    }

    body.dark .view-all:hover {
        color: #d97706;
    }

    .view-all::after {
        content: '\f105';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-left: 5px;
        transition: transform var(--transition-normal);
    }

    .view-all:hover::after {
        transform: translateX(3px);
    }

    /* ===== RECIPE CARDS ===== */
    .recipes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .recipe-card {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: all var(--transition-normal);
        animation: fadeIn 0.6s ease-out;
    }

    .recipe-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--box-shadow-hover);
    }

    .recipe-image {
        height: 220px;
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
    }

    .recipe-image::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 50%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    }

    .recipe-badges {
        position: absolute;
        bottom: 10px;
        left: 10px;
        display: flex;
        gap: 8px;
        z-index: 2;
    }

    .recipe-badges span {
        background-color: rgba(0, 0, 0, 0.6);
        color: var(--white);
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        backdrop-filter: blur(4px);
    }

    .recipe-badges span i {
        margin-right: 5px;
    }

    .new-badge {
        background-color: var(--primary-color) !important;
    }

    body.dark .new-badge {
        background-color: var(--accent) !important;
    }

    .recipe-info {
        padding: 1.5rem;
    }

    .recipe-info h3 {
        font-size: 1.25rem;
        margin-bottom: 0.625rem;
        max-height: 3.25rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        transition: color var(--transition-normal);
    }

    .recipe-card:hover .recipe-info h3 {
        color: var(--primary-color);
    }

    body.dark .recipe-card:hover .recipe-info h3 {
        color: var(--accent);
    }

    .recipe-description {
        color: var(--text-light);
        margin-bottom: 1rem;
        max-height: 3rem;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .recipe-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        color: var(--text-light);
        font-size: 0.875rem;
    }

    .recipe-meta span {
        display: flex;
        align-items: center;
    }

    .recipe-meta span i {
        margin-right: 5px;
    }

    .view-recipe {
        display: block;
        text-align: center;
        background-color: var(--primary-color);
        color: var(--white);
        padding: 0.75rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: all var(--transition-normal);
    }

    .view-recipe:hover {
        background-color: var(--primary-dark);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    body.dark .view-recipe {
        background-color: var(--accent);
    }

    body.dark .view-recipe:hover {
        background-color: #d97706;
    }

    /* ===== CATEGORIES SECTION ===== */
    .categories-section {
        padding: 2rem 0;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .category-card {
        background-color: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem 1.25rem;
        text-align: center;
        transition: all var(--transition-normal);
        display: flex;
        flex-direction: column;
        align-items: center;
        color: var(--secondary-color);
        animation: fadeIn;
    }

    .category-card:hover {
        transform: translateY(-5px);
        background-color: var(--primary-color);
        color: var(--white);
    }

    body.dark .category-card:hover {
        background-color: var(--accent);
    }

    .category-card i {
        font-size: 2.25rem;
        margin-bottom: 1rem;
        transition: transform var(--transition-normal);
    }

    .category-card:hover i {
        transform: scale(1.2);
    }

    .category-card h3 {
        font-size: 1.125rem;
        margin-bottom: 0.625rem;
        color: inherit;
    }

    .category-card .recipe-count {
        font-size: 0.875rem;
        color: var(--text-light);
        transition: color var(--transition-normal);
    }

    .category-card:hover .recipe-count {
        color: rgba(255, 255, 255, 0.8);
    }

    /* ===== FEATURED & LATEST RECIPES ===== */
    .featured-recipes,
    .latest-recipes {
        padding: 2rem 0;
    }

    .featured-recipes {
        background-color: var(--white);
    }

    /* ===== NEWSLETTER SECTION ===== */
    .newsletter-section {
        background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url('/resep_webite_native/assets/images/newsletter-bg.jpg');
        background-size: cover;
        background-position: center;
        color: var(--white);
        padding: 5rem 0;
        text-align: center;
        position: relative;
    }

    .newsletter-content {
        max-width: 600px;
        margin: 0 auto;
        animation: fadeIn 1s ease-out;
    }

    .newsletter-content h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: var(--white);
    }

    .newsletter-content p {
        margin-bottom: 2rem;
        color: #e0e0e0;
        font-size: 1.125rem;
    }

    .newsletter-form .form-group {
        display: flex;
        max-width: 500px;
        margin: 0 auto;
    }

    .newsletter-form input {
        flex: 1;
        padding: 0.9375rem;
        border: none;
        border-radius: 50px 0 0 50px;
        font-size: 1rem;
        transition: all var(--transition-normal);
    }

    .newsletter-form input:focus {
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    body.dark .newsletter-form input {
        background-color: var(--white);
        color: var(--text-color);
    }

    .newsletter-form button {
        background-color: var(--primary-color);
        color: white;
        border-radius: 0 50px 50px 0;
        padding: 0 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        transition: all var(--transition-normal);
    }

    body.dark .newsletter-form button {
        background-color: var(--accent);
    }

    .newsletter-form button:hover {
        background-color: var(--primary-dark);
        transform: translateX(2px);
    }

    body.dark .newsletter-form button:hover {
        background-color: #d97706;
    }

    /* ===== FOOTER ===== */
    .footer {
        background-color: var(--secondary-color);
        color: #a3a3a3;
        padding: 4rem 0 2rem;
    }

    body.dark .footer {
        background-color: var(--bg-primary);
    }

    .footer-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 2.5rem;
    }

    .footer-column h3 {
        color: white;
        margin-bottom: 1.5625rem;
        font-size: 1.25rem;
        position: relative;
        padding-bottom: 0.625rem;
    }

    .footer-column h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background-color: var(--primary-color);
    }

    body.dark .footer-column h3::after {
        background-color: var(--accent);
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-links a {
        color: #a3a3a3;
        transition: all var(--transition-normal);
    }

    .footer-links a:hover {
        color: white;
        padding-left: 5px;
    }

    .social-links {
        display: flex;
        gap: 0.9375rem;
        margin-top: 1.25rem;
    }

    .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        transition: all var(--transition-normal);
    }

    .social-links a:hover {
        background-color: var(--primary-color);
        transform: translateY(-3px);
    }

    body.dark .social-links a:hover {
        background-color: var(--accent);
    }

    .copyright {
        margin-top: 2.5rem;
        text-align: center;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.875rem;
    }

    /* ===== FORM STYLES ===== */
    .recipe-form-container {
        transition: all var(--transition-normal);
    }

    .form-header {
        transition: all var(--transition-normal);
    }

    .form-header:hover {
        background-color: var(--light-bg);
    }

    body.dark .form-header:hover {
        background-color: var(--bg-secondary);
    }

    .recipe-form input,
    .recipe-form select,
    .recipe-form textarea {
        transition: all var(--transition-normal);
    }

    .recipe-form input:focus,
    .recipe-form select:focus,
    .recipe-form textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.2);
    }

    body.dark .recipe-form input,
    body.dark .recipe-form select,
    body.dark .recipe-form textarea {
        background-color: var(--bg-secondary);
        color: var(--text-primary);
        border-color: var(--text-secondary);
    }

    body.dark .recipe-form input:focus,
    body.dark .recipe-form select:focus,
    body.dark .recipe-form textarea:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }

    /* ===== ERROR & NOTIFICATION MESSAGES ===== */
    .error-message {
        background-color: #ffebee;
        color: #d32f2f;
        border: 1px solid #ffcdd2;
        padding: 0.75rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.25rem;
        text-align: center;
        transition: opacity var(--transition-normal);
    }

    .error-message.show {
        opacity: 1;
    }

    .no-recipes,
    .no-categories {
        background-color: #f5f5f5;
        color: var(--text-light);
        padding: 1rem;
        border-radius: var(--border-radius);
        text-align: center;
    }

    body.dark .error-message {
        background-color: #b71c1c;
        color: var(--white);
        border-color: transparent;
    }

    body.dark .no-recipes,
    body.dark .no-categories {
        background-color: var(--bg-primary);
        color: var(--text-secondary);
    }

    /* ===== SKELETON LOADING ===== */
    .recipe-card.loading {
        position: relative;
        overflow: hidden;
    }

    .recipe-card.loading::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: loading 1.5s infinite;
    }

    .recipe-card.loading .recipe-image {
        background: #e0e0e0;
    }

    body.dark .recipe-card.loading .recipe-image {
        background: #374151;
    }

    .recipe-card.loading .recipe-info h3 {
        background: #e0e0e0;
        height: 20px;
        width: 80%;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    body.dark .recipe-card.loading .recipe-info h3 {
        background: #374151;
    }

    .recipe-card.loading .recipe-description {
        background: #e0e0e0;
        height: 3rem;
        border-radius: 4px;
    }

    body.dark .recipe-card.loading .recipe-description {
        background: #374151;
    }

    .recipe-card.loading .recipe-meta span {
        background: #e0e0e0;
        height: 16px;
        width: 40%;
        border-radius: 4px;
    }

    body.dark .recipe-card.loading .recipe-meta span {
        background: #374151;
    }

    .recipe-card.loading .buttons {
        display: flex;
        gap: 0.5rem;
    }

    .recipe-card.loading .btn {
        background: #e0e0e0;
        height: 40px;
        border-radius: var(--border-radius);
    }

    body.dark .recipe-card.loading .btn {
        background: #374151;
    }

    /* ===== RESPONSIVE STYLES ===== */
    @media (max-width: 992px) {
        .hero h1 {
            font-size: 3rem;
        }

        .hero .tagline {
            font-size: 1.125rem;
        }

        .section-header h2 {
            font-size: 1.5rem;
        }

        .recipes-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .header-container {
            padding: 0 1rem;
        }

        .main-nav {
            display: none;
        }

        .mobile-nav-toggle {
            display: block;
        }

        .user-actions .btn-text {
            display: none;
        }

        .hero {
            padding: 4rem 0;
        }

        .hero h1 {
            font-size: 2rem;
        }

        .hero .tagline {
            font-size: 1rem;
        }

        .search-container select,
        .search-container input {
            padding: 0.75rem;
        }

        .search-container button {
            padding: 0.5rem 1rem;
        }

        .search-container form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .search-container select {
            border-radius: 4px;
        }

        .search-container input {
            border-radius: 4px;
        }

        .search-container button {
            border-radius: 4px;
            padding: 0.75rem;
        }

        .recipes-grid {
            grid-template-columns: 1fr;
        }

        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .newsletter-form .form-group {
            display: grid;
            gap: 0.625rem;
        }

        .newsletter-form input {
            border-radius: 50px;
            margin-bottom: 0;
        }

        .newsletter-form button {
            border-radius: 50px;
        }
    }

    @media (max-width: 480px) {
        .hero h1 {
            font-size: 1.75rem;
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .recipe-card {
            max-width: 320px;
            margin: 0 auto;
        }
    }

    /* Mobile Navigation */
    .main-nav.active {
        display: block;
        position: absolute;
        top: var(--header-height);
        left: 0;
        right: 0;
        background-color: var(--white);
        box-shadow: 0 5px 10px var(--shadow-color);
    }

    body.dark .main-nav.active {
        background-color: var(--bg-primary);
    }

    .main-nav.active ul {
        flex-direction: column;
        padding: 1rem 0;
    }

    .main-nav.active li {
        margin: 0;
    }

    .main-nav.active a {
        display: block;
        padding: 0.75rem 1.5rem;
    }

    .main-nav.active a::after {
        display: none;
    }

    /* Smooth scroll */
    html {
        scroll-behavior: smooth;
    }
</style>