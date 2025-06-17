<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses favorite dan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "Harus login untuk melakukan aksi ini.";
    } else {
        $recipe_id = (int)($_POST['recipe_id'] ?? 0);
        try {
            if ($_POST['action'] === 'favorite') {
                $stmt = $pdo->prepare("SELECT id FROM favorites WHERE recipe_id = ? AND user_id = ?");
                $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("DELETE FROM favorites WHERE recipe_id = ? AND user_id = ?");
                    $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                    $success = "Resep dihapus dari favorit.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO favorites (recipe_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                    $success = "Resep ditambahkan ke favorit.";
                }
            } elseif ($_POST['action'] === 'comment') {
                $content = trim($_POST['content'] ?? '');
                if (empty($content) || strlen($content) > 500) {
                    $errors[] = "Komentar harus 1-500 karakter.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)");
                    $stmt->execute([$recipe_id, $_SESSION['user_id'], $content]);
                    $success = "Komentar berhasil ditambahkan.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
            error_log("Error in community.php: " . $e->getMessage());
        }
    }
}

// Filter dan pencarian
$category_id = (int)($_GET['category_id'] ?? 0);
$diet_type = trim($_GET['diet_type'] ?? '');
$search = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$per_page = 9;
$offset = ($page - 1) * $per_page;

$where = ["r.is_published = 1"];
$params = [];
if ($category_id > 0) {
    $where[] = "r.category_id = ?";
    $params[] = $category_id;
}
if ($diet_type) {
    $where[] = "r.diet_type = ?";
    $params[] = $diet_type;
}
if ($search) {
    $where[] = "(r.title LIKE ? OR r.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes r $where_clause");
    $stmt->execute($params);
    $total_recipes = $stmt->fetchColumn();
    $total_pages = ceil($total_recipes / $per_page);

    $query = "SELECT r.*, c.name as category_name, u.username,
                     (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id) as favorite_count,
                     EXISTS(SELECT 1 FROM favorites f WHERE f.recipe_id = r.id AND f.user_id = ?) as user_favorited
              FROM recipes r
              LEFT JOIN categories c ON r.category_id = c.id
              LEFT JOIN users u ON r.user_id = u.id
              $where_clause
              ORDER BY r.created_at DESC
              LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($query);
    $bind_params = array_merge([$_SESSION['user_id'] ?? 0], $params, [(int)$per_page, (int)$offset]);
    foreach ($bind_params as $index => $value) {
        $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil resep: " . $e->getMessage();
    error_log("Error in community.php: " . $e->getMessage());
}

$pageTitle = "Komunitas Resep - Resepku";
require_once '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/style.css">
<style>
    .sidebar {
        flex: 0 0 250px;
        background: var(--bg-primary);
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-light);
    }

    .sidebar h3 {
        font-size: 1.25rem;
        font-weight: var(--font-weight-semibold);
        margin-bottom: 1rem;
        color: var(--text-color);
    }

    .recipe-form select,
    .recipe-form input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: var(--text-md);
        transition: var(--transition-fast);
    }

    .recipe-form select:focus,
    .recipe-form input:focus {
        border-color: var(--secondary-color);
        outline: none;
        box-shadow: var(--shadow-light);
    }

    .recipe-form button {
        width: 100%;
        padding: 0.75rem;
        background: var(--gradient-orange);
        color: var(--primary-color);
        border: none;
        border-radius: var(--border-radius);
        font-weight: var(--font-weight-semibold);
        transition: var(--transition-bounce);
    }

    .recipe-form button:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: var(--shadow-medium);
    }

    .recipe-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .recipe-card {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        background: var(--bg-primary);
        box-shadow: var(--shadow-light);
        transition: var(--transition-normal);
    }

    .recipe-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .recipe-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #f0f0f0;
    }

    .recipe-card-content {
        padding: 1rem;
    }

    .recipe-card h4 {
        font-size: var(--text-lg);
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }

    .recipe-card p {
        font-size: 0.9rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .recipe-card .meta {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }

    .recipe-card .meta i {
        margin-right: 0.5rem;
        color: var(--secondary-color);
    }

    .recipe-card a {
        display: inline-block;
        margin-top: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--gradient-orange);
        color: var(--primary-color);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: var(--transition-bounce);
    }

    .recipe-card a:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: var(--shadow-light);
    }

    .no-recipes {
        text-align: center;
        color: var(--text-light);
    }

    .container {
        max-width: var(--container-width);
        margin-left: auto;
        margin-right: auto;
        padding: 0 2rem; /* Tambahkan padding kiri dan kanan */
    }
</style>

<main class="main-content">
    <section class="container" style="padding: 2rem 0; display: flex; gap: 2rem;">
        <!-- Sidebar Filter -->
        <aside class="sidebar">
            <h3>Filter Resep</h3>
            <form method="GET" class="recipe-form">
                <div style="margin-bottom: 1.5rem;">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id">
                        <option value="">Semua Kategori</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories");
                        while ($category = $stmt->fetch()) {
                            $selected = ($category_id == $category['id']) ? 'selected' : '';
                            echo "<option value='{$category['id']}' $selected>" . htmlspecialchars($category['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="diet_type">Tipe Diet</label>
                    <select id="diet_type" name="diet_type">
                        <option value="">Semua</option>
                        <option value="vegan" <?= $diet_type === 'vegan' ? 'selected' : '' ?>>Vegan</option>
                        <option value="keto" <?= $diet_type === 'keto' ? 'selected' : '' ?>>Keto</option>
                        <option value="gluten-free" <?= $diet_type === 'gluten-free' ? 'selected' : '' ?>>Gluten-Free</option>
                        <option value="vegetarian" <?= $diet_type === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="search">Pencarian</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit">Terapkan</button>
            </form>
        </aside>

        <!-- Daftar Resep -->
        <div style="flex: 1;">
            <h2>Komunitas Resep</h2>
            <?php if (!empty($recipes)): ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div style="background-image: url('/resep_website_native/<?= htmlspecialchars($recipe['image']) ?>'); background-size: cover; background-position: center; height: 180px;"></div>
                            <div class="recipe-card-content">
                                <h4><?= htmlspecialchars($recipe['title']) ?></h4>
                                <p><?= htmlspecialchars(substr($recipe['description'], 0, 80)) ?>...</p>
                                <div class="meta">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($recipe['username']) ?></span>
                                    <span><i class="fas fa-heart"></i> <?= $recipe['favorite_count'] ?></span>
                                    <span><i class="fas fa-comment"></i>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE recipe_id = ?");
                                        $stmt->execute([$recipe['id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </span>
                                </div>
                                <a href="/resep_website_native/pages/recipes/detail.php?id=<?= $recipe['id'] ?>">Lihat Resep</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-recipes">Tidak ada resep ditemukan.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>