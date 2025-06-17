<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<p>ID resep tidak ditemukan.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    echo "<p>Resep tidak ditemukan.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE recipes SET title = ?, description = ?, is_published = ? WHERE id = ?");
    $stmt->execute([$title, $description, $is_published, $id]);
    header("Location: /resep_website_native/pages/admin/manage_recipes.php");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-6 py-10">
    <h2 class="text-2xl font-bold mb-4">Edit Recipe</h2>
    <form method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        <div>
            <label class="block mb-1 font-medium">Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border p-2 rounded"><?= htmlspecialchars($recipe['description']) ?></textarea>
        </div>
        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_published" value="1" <?= $recipe['is_published'] ? 'checked' : '' ?>>
            <label>Published</label>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>