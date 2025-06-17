<?php
require_once '../../includes/config.php';
require_once '../../includes/header.php';

// Ambil kategori dari URL jika ada
$category_id = $_GET['category'] ?? null;
$params = [];
$where = "";

// Buat query filter jika ada category_id
if ($category_id !== null && is_numeric($category_id)) {
    $where = "WHERE r.category_id = ?";
    $params[] = (int)$category_id;
}

// Query untuk mengambil resep + kategori
$sql = "SELECT r.*, c.name AS category_name 
        FROM recipes r 
        JOIN categories c ON r.category_id = c.id 
        $where 
        ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);

// Eksekusi query, tampilkan error jika gagal
if (!$stmt->execute($params)) {
    die("Query Error: " . implode(", ", $stmt->errorInfo()));
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Daftar Resep</h1>

    <!-- Filter Kategori -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Filter Kategori</h2>
        <div class="flex flex-wrap gap-4">
            <a href="pages/recipes/all.php" class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300 <?php if (!$category_id) echo 'font-bold'; ?>">Semua</a>
            <?php
            // Ambil daftar kategori
            $stmt_categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
            while ($category = $stmt_categories->fetch(PDO::FETCH_ASSOC)):
                $active = ($category_id == $category['id']) ? 'font-bold' : '';
            ?>
                <a href="pages/recipes/all.php?category=<?php echo $category['id']; ?>"
                    class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300 <?php echo $active; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Daftar Resep -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($recipe = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <?php if (!empty($recipe['image'])): ?>
                    <img src="/resep_website_native/<?php echo htmlspecialchars($recipe['image']); ?>"
                        alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                        class="w-full h-48 object-cover mb-4 rounded">
                <?php endif; ?>

                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                <p class="text-gray-700 mb-2">Kategori: <?php echo htmlspecialchars($recipe['category_name']); ?></p>
                <p class="text-gray-600 mb-4"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                <a href="/resep_website_native/pages/recipes/detail.php?id=<?php echo $recipe['id']; ?>"
                    class="text-blue-500 hover:underline">Lihat Detail</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>