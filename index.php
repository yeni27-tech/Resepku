<?php
require_once 'includes/session.php';

// Debug: Cek config.php
if (!file_exists('includes/config.php')) {
    die("File config.php tidak ditemukan di path: " . realpath('includes/config.php'));
}
require_once 'includes/config.php';

// Debug: Cek $pdo
if (!isset($pdo)) {
    die("Variabel \$pdo tidak terdefinisi. Cek includes/config.php.");
}

// CSRF token untuk newsletter
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = "Resepku - Temukan Resep Lezat dan Mudah";
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <main class="main-content">
        <!-- Hero Section -->
        <main class="main-content">
            <!-- Hero Section -->
            <section class="hero" style="background-image: url('assets/images/hero-bg.png');">
                <div class="hero-content">
                    <h1>Resepku</h1>
                    <p class="tagline">Temukan inspirasi memasak harian dengan ribuan resep pilihan</p>
                    <div class="search-container">
                        <form action="pages/recipes/search.php" method="GET">
                            <input type="text" name="q" placeholder="Cari resep favorit Anda..." required aria-label="Cari Resep" style="color: black;">
                            <button type="submit"><i class="fas fa-search"></i> Cari</button>
                        </form>
                        <!-- <br>
                    <select name="category" aria-label="Pilih Kategori">
                        <option value="">Semua Kategori</option>
                        <?php
                        // try {
                        //     $stmt = $pdo->query("SELECT * FROM categories");
                        //     while ($category = $stmt->fetch()) {
                        //         echo "<option value='{$category['id']}'>" . htmlspecialchars($category['name']) . "</option>";
                        //     }
                        // } catch (PDOException $e) {
                        //     error_log("Error fetching categories: " . $e->getMessage());
                        // }
                        ?>
                    </select> -->
                    </div>
                </div>
            </section>


            <!-- Quick Links -->
            <section class="quick-links">
                <div class="container">
                    <a href="pages/recipes/all.php" class="quick-link" aria-label="Semua Resep">
                        <i class="fas fa-book"></i>
                        <span>Semua Resep</span>
                    </a>
                    <a href="pages/recipes/latest.php" class="quick-link" aria-label="Resep Terbaru">
                        <i class="fas fa-clock"></i>
                        <span>Terbaru</span>
                    </a>
                    <a href="pages/recipes/popular.php" class="quick-link" aria-label="Resep Populer">
                        <i class="fas fa-star"></i>
                        <span>Terpopuler</span>
                    </a>
                    <a href="pages/user/favorites.php" class="quick-link" aria-label="Favorit Saya">
                        <i class="fas fa-heart"></i>
                        <span>Jelajahi Resep</span>
                    </a>
                </div>
            </section>

            <!-- Resep Komunitas -->
            <section class="featured-recipes">
                <div class="container">
                    <div class="section-header">
                        <h2>Resep Komunitas</h2>
                        <a href="pages/community/community.php" class="view-all">Lihat Semua</a>
                    </div>
                    <div class="recipes-grid">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT r.*, u.username 
                                         FROM recipes r 
                                         JOIN users u ON r.user_id = u.id 
                                         WHERE r.is_published = 1 
                                         ORDER BY r.created_at DESC LIMIT 3");
                            if ($stmt->rowCount() === 0) {
                                echo "<p class='no-recipes'>Belum ada resep komunitas.</p>";
                            } else {
                                while ($recipe = $stmt->fetch()) {
                                    $imageUrl = $recipe['image'] ? "/resep_website_native/{$recipe['image']}" : '/resep_website_native/assets/images/default-recipe.jpg';
                                    $cookTime = $recipe['cooking_time'] ? $recipe['cooking_time'] . ' menit' : 'N/A';
                                    echo "<div class='recipe-card'>
                                <div class='recipe-image' style='background-image: url(\"$imageUrl\");'>
                                    <div class='recipe-badges'>
                                        <span class='time-badge'><i class='fas fa-clock'></i> $cookTime</span>
                                    </div>
                                </div>
                                <div class='recipe-info'>
                                    <h3>" . htmlspecialchars($recipe['title']) . "</h3>
                                    <p class='recipe-description'>" . htmlspecialchars(substr($recipe['description'], 0, 80)) . "...</p>
                                    <div class='recipe-meta'>
                                        <span><i class='fas fa-user'></i> " . htmlspecialchars($recipe['username']) . "</span>
                                        <a href='https://twitter.com/intent/tweet?url=" . urlencode("http://localhost/resep_website_native/pages/recipes/detail.php?id={$recipe['id']}") . "&text=" . urlencode($recipe['title']) . "' 
                                           target='_blank' aria-label='Share to Twitter'><i class='fab fa-twitter'></i></a>
                                    </div>
                                    <a href='pages/recipes/detail.php?id={$recipe['id']}' class='view-recipe'>Lihat Resep</a>
                                </div>
                            </div>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log("Error fetching community recipes: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Featured Recipes -->
            <section class="featured-recipes">
                <div class="container">
                    <div class="section-header">
                        <h2>Resep Populer</h2>
                        <a href="pages/recipes/popular.php" class="view-all">Lihat Semua</a>
                    </div>
                    <div class="recipes-grid">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT r.*, COUNT(f.id) as favorite_count, u.username 
                                         FROM recipes r 
                                         LEFT JOIN favorites f ON r.id = f.recipe_id 
                                         LEFT JOIN users u ON r.user_id = u.id 
                                         GROUP BY r.id 
                                         ORDER BY favorite_count DESC LIMIT 3");
                            if ($stmt->rowCount() === 0) {
                                echo "<p class='no-recipes'>Belum ada resep populer.</p>";
                            } else {
                                while ($recipe = $stmt->fetch()) {
                                    $imageUrl = $recipe['image'] ? "/resep_website_native/{$recipe['image']}" : '/resep_website_native/assets/images/default-recipe.jpg';
                                    $cookTime = $recipe['cooking_time'] ? $recipe['cooking_time'] . ' menit' : 'N/A';
                                    echo "<div class='recipe-card'>
                                <div class='recipe-image' style='background-image: url(\"$imageUrl\");'>
                                    <div class='recipe-badges'>
                                        <span class='time-badge'><i class='fas fa-clock'></i> $cookTime</span>
                                    </div>
                                </div>
                                <div class='recipe-info'>
                                    <h3>" . htmlspecialchars($recipe['title']) . "</h3>
                                    <p class='recipe-description'>" . htmlspecialchars(substr($recipe['description'], 0, 80)) . "...</p>
                                    <div class='recipe-meta'>
                                        <span><i class='fas fa-user'></i> " . htmlspecialchars($recipe['username'] ?: 'Admin') . "</span>
                                        <span><i class='fas fa-heart'></i> {$recipe['favorite_count']}</span>
                                    </div>
                                    <a href='pages/recipes/detail.php?id={$recipe['id']}' class='view-recipe' style='hover: color: var(--white);'>Lihat Resep</a>
                                </div>
                            </div>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log("Error fetching popular recipes: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Categories -->
            <section class="categories-section">
                <div class="container">
                    <div class="section-header">
                        <h2>Kategori Resep</h2>
                        <a href="pages/categories/all.php" class="view-all">Lihat Semua</a>
                    </div>
                    <div class="categories-grid">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT c.*, COUNT(r.id) as recipe_count 
                                         FROM categories c 
                                         LEFT JOIN recipes r ON c.id = r.category_id 
                                         GROUP BY c.id 
                                         ORDER BY recipe_count DESC LIMIT 4");
                            if ($stmt->rowCount() === 0) {
                                echo "<p class='no-categories'>Belum ada kategori.</p>";
                            } else {
                                while ($category = $stmt->fetch()) {
                                    $icon = 'fas fa-utensils';
                                    if (stripos($category['name'], 'sarapan') !== false) $icon = 'fas fa-coffee';
                                    elseif (stripos($category['name'], 'dessert') !== false || stripos($category['name'], 'kue') !== false) $icon = 'fas fa-cake-candles';
                                    elseif (stripos($category['name'], 'seafood') !== false) $icon = 'fas fa-fish';
                                    echo "<a href='pages/recipes/all.php?category={$category['id']}' class='category-card'>
                                <i class='$icon'></i>
                                <h3>" . htmlspecialchars($category['name']) . "</h3>
                                <span class='recipe-count'>{$category['recipe_count']} Resep</span>
                            </a>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log("Error fetching categories: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Latest Recipes -->
            <section class="latest-recipes">
                <div class="container">
                    <div class="section-header">
                        <h2>Resep Terbaru</h2>
                        <a href="pages/recipes/latest.php" class="view-all">Lihat Semua</a>
                    </div>
                    <div class="recipes-grid">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT r.*, u.username 
                                         FROM recipes r 
                                         LEFT JOIN users u ON r.user_id = u.id 
                                         ORDER BY r.created_at DESC LIMIT 3");
                            if ($stmt->rowCount() === 0) {
                                echo "<p class='no-recipes'>Belum ada resep terbaru.</p>";
                            } else {
                                while ($recipe = $stmt->fetch()) {
                                    $imageUrl = $recipe['image'] ? "/resep_website_native/{$recipe['image']}" : '/resep_website_native/assets/images/default-recipe.jpg';
                                    $cookTime = $recipe['cooking_time'] ? $recipe['cooking_time'] . ' menit' : 'N/A';
                                    echo "<div class='recipe-card'>
                                <div class='recipe-image' style='background-image: url(\"$imageUrl\");'>
                                    <div class='recipe-badges'>
                                        <span class='time-badge'><i class='fas fa-clock'></i> $cookTime</span>
                                        <span class='new-badge'>Baru</span>
                                    </div>
                                </div>
                                <div class='recipe-info'>
                                    <h3>" . htmlspecialchars($recipe['title']) . "</h3>
                                    <p class='recipe-description'>" . htmlspecialchars(substr($recipe['description'], 0, 80)) . "...</p>
                                    <div class='recipe-meta'>
                                        <span><i class='fas fa-user'></i> " . htmlspecialchars($recipe['username'] ?: 'Admin') . "</span>
                                        <span><i class='fas fa-calendar'></i> " . date('d/m/Y', strtotime($recipe['created_at'])) . "</span>
                                    </div>
                                    <a href='pages/recipes/detail.php?id={$recipe['id']}' class='view-recipe'>Lihat Resep</a>
                                </div>
                            </div>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            error_log("Error fetching latest recipes: " . $e->getMessage());
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Newsletter -->
            <section class="newsletter-section">
                <div class="container">
                    <div class="newsletter-content">
                        <h2>Dapatkan Resep Terbaru</h2>
                        <p>Berlangganan newsletter kami untuk resep dan tips memasak mingguan.</p>
                        <form action="pages/newsletter.php" method="POST" class="newsletter-form" style="color: black;">
                            <div class="form-group">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="email" name="email" placeholder="Alamat Email Anda" required aria-label="Email untuk Newsletter">
                                <button type="submit">Berlangganan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        <?php require_once 'includes/footer.php'; ?>
</body>

</html>