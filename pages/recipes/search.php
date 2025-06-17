<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';
require_once '../../includes/header.php';

$search = $_GET['q'] ?? '';
$recipes = [];

if ($search) {
    $stmt = $pdo->prepare("SELECT r.*, c.name as category_name 
                          FROM recipes r 
                          JOIN categories c ON r.category_id = c.id 
                          WHERE r.title LIKE ? OR r.description LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8 animate-fadeIn">Hasil Pencarian: <?php echo htmlspecialchars($search); ?></h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($recipes as $recipe): ?>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300 animate-slideUp card">
                <h3 class="text-xl font-semibold mb-2"><?php echo $recipe['title']; ?></h3>
                <p class="text-gray-600 dark:text-gray-300 mb-2">Kategori: <?php echo $recipe['category_name']; ?></p>
                <p class="text-gray-600 dark:text-gray-300 mb-4"><?php echo $recipe['description']; ?></p>
                <a href="pages/recipes/detail.php?id=<?php echo $recipe['id']; ?>" class="text-blue-500 hover:underline">Lihat Detail</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>