<?php
ob_start();
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /resep_website_native/index.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="flex-1 p-6 bg-gray-100 min-h-screen">
    <h1 class="text-4xl font-bold mb-8 text-gray-800 animate-fadeIn">Admin Dashboard</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <?php
        $stats = [
            'Total Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'Total Recipes' => $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn(),
            'Pending Recipes' => $pdo->query("SELECT COUNT(*) FROM recipes WHERE is_published = 0")->fetchColumn(),
            'New Subscriptions' => $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE DATE(subscribed_at) = CURDATE()")->fetchColumn()
        ];
        foreach ($stats as $label => $value):
        ?>
            <div class="card p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                <h2 class="text-lg font-semibold text-gray-700"><?php echo $label; ?></h2>
                <p class="text-3xl font-bold text-primary mt-2"><?php echo $value; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="mt-10">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Quick Actions</h2>
        <div class="flex space-x-4">
            <a href="/resep_website_native/pages/admin/manage_users.php" class="btn bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-xl">Manage Users</a>
            <a href="/resep_website_native/pages/admin/manage_recipes.php" class="btn bg-green-500 hover:bg-green-600 px-6 py-3 rounded-xl">Manage Recipes</a>
            <a href="/resep_website_native/pages/admin/manage_categories.php" class="btn bg-yellow-500 hover:bg-orange-600 px-6 py-3 rounded-xl">Manage Categories</a>
            <a href="/resep_website_native/pages/admin/statistics.php" class="btn bg-purple-500 hover:bg-purple-600 px-6 py-3 rounded-xl">View Statistics</a>
        </div>
            <style>
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                    color: #fff;
                    transition: background-color 0.3s ease;
                }
            </style>
    </section>

    <section class="mt-10">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Recent Activities</h2>
        <div class="card p-6 bg-white rounded-xl shadow-lg">
            <ul class="list-disc pl-5 space-y-2">
                <?php
                $activities = $pdo->query("SELECT action, created_at FROM activity_log ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($activities as $activity):
                ?>
                    <li class="text-gray-700"><?php echo htmlspecialchars($activity['action']); ?> - <?php echo date('H:i A, d M Y', strtotime($activity['created_at'])); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/../../includes/footer.php';
ob_end_flush();
?>