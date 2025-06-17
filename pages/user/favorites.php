<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';
require_once '../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $recipe_id = $_POST['recipe_id'];
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$_SESSION['user_id'], $recipe_id]);
    $success = "Resep dihapus dari favorit!";
}
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
        /* Warna merah untuk tombol hapus */
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
        --card-width: 280px;
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

    .section-header h1 {
        font-size: 2rem;
        color: var(--secondary-color);
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

    /* Grid Resep */
    .recipes-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: center;
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
        width: var(--card-width);
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

    .time-badge {
        background-color: var(--secondary-dark);
        color: var(--primary-color);
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
        border-radius: var(--border-radius);
        font-weight: var(--font-weight-medium);
        transition: var(--transition-fast);
    }

    .time-badge:hover {
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
        display: -webkit-box;
        /* -webkit-line-clamp: 2; */
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .recipe-meta {
        font-size: 0.85rem;
        color: var(--text-light);
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .view-recipe,
    .remove-recipe {
        display: inline-block;
        padding: 0.5rem 0.8rem;
        color: var(--primary-color);
        text-decoration: none;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        font-weight: var(--font-weight-medium);
        transition: var(--transition-fast);
        position: relative;
        overflow: hidden;
    }

    .view-recipe {
        background: var(--gradient-orange);
    }

    .remove-recipe {
        background: var(--secondary-dark);
        /* Tombol merah untuk hapus */
        margin-top: 0.5rem;
    }

    .view-recipe::before,
    .remove-recipe::before {
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

    .view-recipe:hover,
    .remove-recipe:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
    }

    .view-recipe:hover::before,
    .remove-recipe:hover::before {
        width: 200px;
        height: 200px;
    }

    .view-recipe:hover {
        background: var(--gradient-hover);
    }

    .remove-recipe:hover {
        background: #ff4d4d;
        /* Warna merah lebih terang saat hover */
    }

    /* Success Message */
    .success-message {
        text-align: center;
        font-size: 1rem;
        color: var(--accent-color);
        margin-bottom: 1rem;
        animation: fadeIn 0.5s ease-out;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .recipe-card {
            width: calc((100% - 6rem) / 3);
        }
    }

    @media (max-width: 900px) {
        .recipe-card {
            width: calc((100% - 4rem) / 2);
        }
    }

    @media (max-width: 600px) {
        .recipe-card {
            width: 100%;
        }
    }
</style>

<script>
    function confirmRemove(recipeId) {
        if (confirm('Apakah Anda yakin ingin menghapus resep ini dari favorit?')) {
            document.getElementById('removeForm_' + recipeId).submit();
        }
    }
</script>

<div class="container">
    <div class="section-header">
        <h1>Resep Favorit</h1>
    </div>

    <?php if (isset($success)): ?>
        <p class="success-message"><?php echo $success; ?></p>
    <?php endif; ?>

    <div class="recipes-grid">
        <?php
        $stmt = $pdo->prepare("SELECT r.*, c.name as category_name 
                              FROM recipes r 
                              JOIN favorites f ON r.id = f.recipe_id 
                              JOIN categories c ON r.category_id = c.id 
                              WHERE f.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        while ($recipe = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $image_url = $recipe['image'] ? "/resep_website_native/{$recipe['image']}" : '/resep_website_native/assets/images/default-recipe.jpg';
            $cook_time = $recipe['cooking_time'] ? $recipe['cooking_time'] . ' menit' : 'N/A';
            echo "
            <div class='recipe-card'>
                <div class='recipe-image' style='background-image: url(\"" . htmlspecialchars($image_url) . "\");'>
                    <div class='recipe-badges'>
                        <span class='time-badge'><i class='fas fa-clock'></i> $cook_time</span>
                    </div>
                </div>
                <div class='recipe-info'>
                    <h3>" . htmlspecialchars($recipe['title']) . "</h3>
                    <p class='recipe-description'>" . htmlspecialchars($recipe['description']) . "</p>
                    <div class='recipe-meta'>
                        <span><i class='fas fa-folder'></i> " . htmlspecialchars($recipe['category_name']) . "</span>
                    </div>
                    <a href='/resep_website_native/pages/recipes/detail.php?id=" . $recipe['id'] . "' class='view-recipe'>Lihat Detail</a>
                    <form method='POST' id='removeForm_" . $recipe['id'] . "' class='mt-2'>
                        <input type='hidden' name='recipe_id' value='" . $recipe['id'] . "'>
                       
                    </form>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>