<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

// Pastikan pengguna login
if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/user/login.php');
    exit;
}

// Cek recipe_id
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id <= 0) {
    header('Location: /resep_website_native/pages/user/manage_recipes.php');
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil data resep
try {
    $stmt = $pdo->prepare("SELECT r.*, c.name as category_name 
                           FROM recipes r 
                           LEFT JOIN categories c ON r.category_id = c.id 
                           WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$recipe_id, $_SESSION['user_id']]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        header('Location: /resep_website_native/pages/user/manage_recipes.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error mengambil resep: " . $e->getMessage();
    error_log("Error in edit.php: " . $e->getMessage());
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $cooking_time = (int)($_POST['cooking_time'] ?? 0);
    $calories = (int)($_POST['calories'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    $errors = [];
    if (empty($title)) $errors[] = "Judul wajib diisi.";
    if ($category_id <= 0) $errors[] = "Kategori wajib dipilih.";
    if (empty($description)) $errors[] = "Deskripsi wajib diisi.";
    if (empty($ingredients)) $errors[] = "Bahan wajib diisi.";
    if (empty($instructions)) $errors[] = "Langkah wajib diisi.";
    if ($cooking_time <= 0) $errors[] = "Waktu masak harus lebih dari 0 menit.";

    // Proses gambar
    $image_path = $recipe['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];

        if (!in_array($file_ext, $allowed)) {
            $errors[] = "Format gambar harus JPG, JPEG, atau PNG.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran gambar maksimal 2MB.";
        } else {
            $new_file_name = 'recipe_' . time() . '.' . $file_ext;
            $upload_path = 'assets/images/recipes/' . $new_file_name;
            if (move_uploaded_file($file_tmp, $_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $upload_path)) {
                $image_path = $upload_path;
                // Hapus gambar lama jika ada
                if ($recipe['image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $recipe['image'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $recipe['image']);
                }
            } else {
                $errors[] = "Gagal mengunggah gambar.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE recipes 
                                   SET category_id = ?, title = ?, description = ?, 
                                       ingredients = ?, instructions = ?, cooking_time = ?, 
                                       calories = ?, image = ?, is_published = ? 
                                   WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $category_id,
                $title,
                $description,
                $ingredients,
                $instructions,
                $cooking_time,
                $calories,
                $image_path,
                $is_published,
                $recipe_id,
                $_SESSION['user_id']
            ]);
            $success = "Resep berhasil diperbarui.";
            // Refresh data resep
            $stmt = $pdo->prepare("SELECT r.*, c.name as category_name 
                                   FROM recipes r 
                                   LEFT JOIN categories c ON r.category_id = c.id 
                                   WHERE r.id = ?");
            $stmt->execute([$recipe_id]);
            $recipe = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = "Error menyimpan resep: " . $e->getMessage();
            error_log("Error updating recipe: " . $e->getMessage());
        }
    }
}

$pageTitle = "Edit Resep - Resepku";
require_once '../../includes/header.php';
?>

<main class="main-content">
    <section class="container" style="padding: 2rem 0;">
        <div class="section-header">
            <h2>Edit Resep</h2>
        </div>
        <?php if (isset($success)): ?>
            <p class="error-message" style="background-color: #e6ffed; color: #2e7d32; border-color: #a5d6a7;" data-toast><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error-message" data-toast><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data" class="recipe-form" style="max-width: 800px; margin: 0 auto;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div style="margin-bottom: 1.5rem;">
                <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Judul Resep</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>"
                    required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
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
                            $selected = ($category['id'] == $recipe['category_id']) ? 'selected' : '';
                            echo "<option value='{$category['id']}' $selected>" . htmlspecialchars($category['name']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        error_log("Error fetching categories: " . $e->getMessage());
                    }
                    ?>
                </select>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Deskripsi</label>
                <textarea id="description" name="description" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 100px;"><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="ingredients" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Bahan-bahan</label>
                <textarea id="ingredients" name="ingredients" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 150px;"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="instructions" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Langkah-langkah</label>
                <textarea id="instructions" name="instructions" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md); min-height: 150px;"><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="cooking_time" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Waktu Masak (menit)</label>
                <input type="number" id="cooking_time" name="cooking_time" value="<?php echo (int)$recipe['cooking_time']; ?>"
                    required min="1" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="calories" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kalori (opsional)</label>
                <input type="number" id="calories" name="calories" value="<?php echo (int)$recipe['calories']; ?>"
                    min="0" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label for="image" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Gambar Resep (opsional)</label>
                <?php if ($recipe['image']): ?>
                    <img src="/resep_website_native/<?php echo htmlspecialchars($recipe['image']); ?>"
                        alt="Gambar Resep" id="image-preview"
                        style="max-width: 200px; margin-bottom: 1rem; border-radius: var(--border-radius);">
                <?php else: ?>
                    <img src="/resep_website_native/assets/images/default-recipe.jpg"
                        alt="Gambar Default" id="image-preview"
                        style="max-width: 200px; margin-bottom: 1rem; border-radius: var(--border-radius); display: none;">
                <?php endif; ?>
                <input type="file" id="image-input" name="image" accept="image/jpeg,image/png"
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); font-size: var(--text-md);">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; font-weight: 600;">
                    <input type="checkbox" name="is_published" <?php echo $recipe['is_published'] ? 'checked' : ''; ?>
                        style="margin-right: 0.5rem;">
                    Publikasikan Resep
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Simpan Perubahan</button>
        </form>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>