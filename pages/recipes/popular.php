<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

$per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$category = isset($_GET['category']) ? (int)$_GET['category'] : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    // Hitung total data
    $count_query = "SELECT COUNT(DISTINCT r.id) FROM recipes r WHERE 1=1";
    $params = [];
    if ($category) {
        $count_query .= " AND r.category_id = ?";
        $params[] = $category;
    }
    if ($search) {
        $count_query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_recipes = $stmt->fetchColumn();
    $total_pages = ceil($total_recipes / $per_page);

    // Ambil data resep populer
    $query = "SELECT r.*, u.username, c.name as category_name, COUNT(f.id) as favorite_count 
              FROM recipes r 
              LEFT JOIN users u ON r.user_id = u.id 
              LEFT JOIN categories c ON r.category_id = c.id 
              LEFT JOIN favorites f ON r.id = f.recipe_id 
              WHERE 1=1";
    $params = [];
    if ($category) {
        $query .= " AND r.category_id = ?";
        $params[] = $category;
    }
    if ($search) {
        $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $query .= " GROUP BY r.id ORDER BY favorite_count DESC LIMIT $per_page OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
    error_log("Error in popular.php: " . $e->getMessage());
}

$pageTitle = "Resep Populer - Resepku";
require_once '../../includes/header.php';
?>

<style>
    /* Reset CSS */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        /* Primary Colors - White & Orange Theme */
        --primary-color: #ffffff;
        --primary-dark: #f8f9fa;
        --primary-light: #ffffff;
        --secondary-color: #ff6b35;
        --secondary-light: #ff8c62;
        --secondary-dark: #e54e1b;
        --accent-color: #48bb78;

        /* Text Colors */
        --text-color: #2d3748;
        --text-light: #718096;
        --text-muted: #a0aec0;

        /* Background Colors */
        --bg-primary: #ffffff;
        --bg-secondary: #f7fafc;
        --bg-overlay: rgba(255, 255, 255, 0.95);

        /* Border & Shadow */
        --border-color: rgba(255, 107, 53, 0.1);
        --shadow-light: 0 2px 8px rgba(255, 107, 53, 0.08);
        --shadow-medium: 0 4px 16px rgba(255, 107, 53, 0.12);
        --shadow-heavy: 0 8px 32px rgba(255, 107, 53, 0.15);
        --shadow-glow: 0 0 20px rgba(255, 107, 53, 0.3);

        /* Gradients */
        --gradient-orange: linear-gradient(135deg, #ff6b35 0%, #ff8c62 100%);
        --gradient-white: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        --gradient-hover: linear-gradient(135deg, #e54e1b 0%, #ff6b35 100%);

        /* Typography */
        --font-weight-light: 300;
        --font-weight-normal: 400;
        --font-weight-medium: 500;
        --font-weight-semibold: 600;
        --font-weight-bold: 700;
        --font-weight-extrabold: 800;
        --font-weight-black: 900;

        /* Sizing */
        --header-height: 80px;
        --container-width: 1280px;
        --border-radius: 12px;
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;

        /* Transitions */
        --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-color);
        background-color: var(--bg-secondary);
        line-height: 1.6;
    }

    .container {
        max-width: var(--container-width);
        margin: auto;
        padding: 2rem 1rem;
    }

    .section-header {
        text-align: center;
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease-in-out;
    }

    .section-header h2 {
        font-size: 2rem;
        color: var(--secondary-color);
        border-bottom: 2px solid var(--secondary-color);
        display: inline-block;
        padding-bottom: 0.3rem;
        font-weight: var(--font-weight-extrabold);
    }

    /* Animasi Fade In */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form Pencarian */
    .search-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
        background: var(--bg-primary);
        padding: 1rem;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-light);
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-container input[type="text"],
    .search-container select {
        padding: 0.6rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 1rem;
        flex: 1;
        background-color: var(--bg-primary);
        transition: var(--transition-fast);
    }

    .search-container input[type="text"]:focus,
    .search-container select:focus {
        outline: none;
        box-shadow: var(--shadow-light);
        border-color: var(--secondary-light);
    }

    .search-container button {
        padding: 0.6rem 1rem;
        background: var(--gradient-orange);
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        border-radius: var(--border-radius);
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: var(--font-weight-semibold);
        transition: var(--transition-normal);
    }

    .search-container button:hover {
        background: var(--gradient-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    /* Grid Resep */
    .recipes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        animation: gridFadeIn 0.7s ease-in-out;
    }

    @keyframes gridFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .recipe-card {
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-light);
        transition: var(--transition-bounce);
        animation: cardPop 0.5s ease-out;
    }

    @keyframes cardPop {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .recipe-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    /* Gambar Resep */
    .recipe-image {
        background-size: cover;
        background-position: center;
        height: 180px;
        position: relative;
        transition: var(--transition-normal);
    }

    .recipe-image:hover {
        filter: brightness(1.1);
    }

    .recipe-badges {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .time-badge,
    .new-badge {
        background-color: var(--secondary-dark);
        color: var(--primary-color);
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
        border-radius: var(--border-radius);
        font-weight: var(--font-weight-medium);
        transition: var(--transition-fast);
    }

    .time-badge:hover,
    .new-badge:hover {
        background-color: var(--secondary-light);
        transform: scale(1.05);
    }

    /* Info Resep */
    .recipe-info {
        padding: 1rem;
    }

    .recipe-info h3 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
        font-weight: var(--font-weight-semibold);
        transition: var(--transition-fast);
    }

    .recipe-info h3:hover {
        color: var(--secondary-light);
    }

    .recipe-description {
        font-size: 0.95rem;
        color: var(--text-muted);
        margin-bottom: 0.8rem;
        line-height: 1.4;
    }

    .recipe-meta {
        font-size: 0.85rem;
        color: var(--text-light);
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .view-recipe {
        display: inline-block;
        padding: 0.5rem 0.8rem;
        background: var(--gradient-orange);
        color: var(--primary-color);
        text-decoration: none;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        font-weight: var(--font-weight-medium);
        transition: var(--transition-fast);
        position: relative;
        overflow: hidden;
    }

    .view-recipe::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: var(--transition-fast);
    }

    .view-recipe:hover {
        background: var(--gradient-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
    }

    .view-recipe:hover::before {
        width: 200px;
        height: 200px;
    }

    /* Pagination */
    .pagination a {
        display: inline-block;
        padding: 0.5rem 1rem;
        margin: 0 0.2rem;
        background-color: var(--bg-primary);
        border: 1px solid var(--secondary-color);
        border-radius: var(--border-radius);
        color: var(--secondary-color);
        text-decoration: none;
        transition: var(--transition-normal);
        font-weight: var(--font-weight-medium);
        animation: fadeIn 0.5s ease-in-out;
    }

    .pagination a:hover {
        background: var(--secondary-light);
        color: var(--primary-color);
        box-shadow: var(--shadow-light);
        transform: translateY(-2px);
    }

    .pagination a[style*="background-color: var(--secondary-color)"] {
        background: var(--secondary-color);
        color: var(--primary-color);
        pointer-events: none;
    }

    /* Error & No Recipe */
    .error-message,
    .no-recipes {
        text-align: center;
        font-size: 1rem;
        color: var(--secondary-dark);
        margin-top: 2rem;
        background-color: var(--bg-primary);
        padding: 1rem;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--secondary-dark);
        animation: slideUp 0.5s ease-out;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .recipe-meta {
            flex-direction: column;
            gap: 0.3rem;
        }

        .search-container {
            flex-direction: column;
        }

        .search-container button {
            justify-content: center;
        }
    }
</style>

<main class="main-content">
    <section class="featured-recipes">
        <div class="container">
            <div class="section-header">
                <h2>Resep Populer</h2>
            </div>

            <form action="" method="GET" class="search-container" style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <select name="category" class="form-select" style="min-width: 200px;">
                    <option value="">Semua Kategori</option>
                    <?php
                    try {
                        $stmt_cat = $pdo->query("SELECT * FROM categories");
                        while ($cat = $stmt_cat->fetch()) {
                            $selected = ($category == $cat['id']) ? 'selected' : '';
                            echo "<option value='{$cat['id']}' $selected>" . htmlspecialchars($cat['name']) . "</option>";
                        }
                    } catch (PDOException $e) {
                        error_log("Error fetching categories: " . $e->getMessage());
                    }
                    ?>
                </select>
                <input type="text" name="q" class="form-input" placeholder="Cari resep..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; min-width: 200px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            </form>

            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <?php else: ?>
                <div class="recipes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                    <?php
                    if ($stmt->rowCount() === 0) {
                        echo "<p class='no-recipes'>Tidak ada resep ditemukan.</p>";
                    } else {
                        while ($recipe = $stmt->fetch()) {
                            $image_url = $recipe['image'] ? "/resep_website_native/{$recipe['image']}" : '/resep_website_native/assets/images/default-recipe.jpg';
                            $cook_time = $recipe['cooking_time'] ? $recipe['cooking_time'] . ' menit' : 'N/A';
                    ?>
                            <div class="recipe-card" style="border: 1px solid #ddd; border-radius: 12px; overflow: hidden; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s;">
                                <div class="recipe-image" style="background-image: url('<?php echo $image_url; ?>'); height: 180px; background-size: cover; background-position: center;">
                                    <div class="recipe-badges" style="padding: 0.5rem;">
                                        <span class="time-badge" style="background-color: #ff6f61; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;"><i class="fas fa-clock"></i> <?php echo $cook_time; ?></span>
                                    </div>
                                </div>
                                <div class="recipe-info" style="padding: 1rem;">
                                    <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="recipe-description" style="color: #555; font-size: 0.95rem;"><?php echo htmlspecialchars(substr($recipe['description'], 0, 80)); ?>...</p>
                                    <div class="recipe-meta" style="margin-top: 0.5rem; display: flex; justify-content: space-between; font-size: 0.85rem; color: #777;">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($recipe['username'] ?: 'Admin'); ?></span>
                                        <span><i class="fas fa-heart"></i> <?php echo $recipe['favorite_count']; ?></span>
                                    </div>
                                    <a href="detail.php?id=<?php echo $recipe['id']; ?>" class="view-recipe" style="display: inline-block; margin-top: 0.8rem; background-color: var(--primary-color); color: #fff; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none;">Lihat Resep</a>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="text-align: center; margin-top: 2rem;">
                        <?php
                        $base_url = "?category=$category&q=" . urlencode($search) . "&page=";
                        if ($page > 1) {
                            echo "<a href='{$base_url}" . ($page - 1) . "' class='btn btn-outline' style='margin-right: 0.5rem;'>Previous</a>";
                        }
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active = ($i == $page) ? 'background-color: var(--primary-color); color: #fff;' : '';
                            echo "<a href='{$base_url}$i' class='btn btn-outline' style='margin: 0 0.2rem; $active'>$i</a>";
                        }
                        if ($page < $total_pages) {
                            echo "<a href='{$base_url}" . ($page + 1) . "' class='btn btn-outline' style='margin-left: 0.5rem;'>Next</a>";
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>