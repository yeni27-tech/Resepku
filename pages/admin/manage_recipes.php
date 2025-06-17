<?php
// File: pages/admin/manage_recipes.php
ob_start();
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /resep_website_native/index.php');
    exit;
}
require_once __DIR__ . '/../../includes/header.php';
?>

<main class="flex-1 p-6 bg-gray-100">
    <h1 class="text-3xl font-bold mb-6">Manage Recipes</h1>
    <table class="min-w-full bg-white rounded-2xl overflow-hidden shadow">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Title</th>
                <th class="px-4 py-2 text-left">Author</th>
                <th class="px-4 py-2 text-left">Published</th>
                <th class="px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT r.id, r.title, u.username, r.is_published FROM recipes r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr class="border-t">
                    <td class="px-4 py-2"><?= $row['id']; ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['title']); ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['username']); ?></td>
                    <td class="px-4 py-2"><?= $row['is_published'] ? 'Yes' : 'No'; ?></td>
                    <td class="px-4 py-2 flex space-x-2 justify-center">
                        <a href="pages/admin/edit_recipe.php?id=<?= $row['id']; ?>" class="px-3 py-1 bg-yellow-400 text-white rounded">Edit</a>
                        <a href="pages/admin/delete_recipe.php?id=<?= $row['id']; ?>" onclick="return confirm('Delete this recipe?');" class="px-3 py-1 bg-red-500 text-white rounded">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
ob_end_flush(); ?>