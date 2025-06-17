<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$recipe_count = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$popular_recipes = $pdo->query("SELECT r.title, COUNT(f.id) as favorite_count 
                               FROM recipes r 
                               LEFT JOIN favorites f ON r.id = f.recipe_id 
                               GROUP BY r.id 
                               ORDER BY favorite_count DESC 
                               LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Data grafik dinamis
$monthly_users = $pdo->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count 
                             FROM users GROUP BY DATE_FORMAT(created_at, '%Y-%m')")->fetchAll(PDO::FETCH_ASSOC);
$months = array_column($monthly_users, 'month');
$user_counts = array_column($monthly_users, 'count');
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center mb-8 text-gray-800 animate-fadeIn">Statistics</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <div class="card p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <h2 class="text-2xl font-semibold mb-2 text-gray-700">Total Users</h2>
            <p class="text-4xl font-bold text-primary"><?php echo $user_count; ?></p>
        </div>
        <div class="card p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <h2 class="text-2xl font-semibold mb-2 text-gray-700">Total Recipes</h2>
            <p class="text-4xl font-bold text-primary"><?php echo $recipe_count; ?></p>
        </div>
    </div>

    <div class="card p-6 bg-white rounded-xl shadow-lg mb-12">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700">Popular Recipes</h2>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left text-gray-600">Title</th>
                    <th class="p-3 text-left text-gray-600">Favorites</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popular_recipes as $recipe): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="p-3"><?php echo htmlspecialchars($recipe['title']); ?></td>
                        <td class="p-3"><?php echo $recipe['favorite_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-semibold mb-4 text-gray-700">User Growth</h2>
        <canvas id="userChart" height="200"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('userChart').getContext('2d');
    const userChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($user_counts); ?>,
                borderColor: '#ff6b35',
                backgroundColor: 'rgba(255, 107, 53, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#333'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#333'
                    },
                    grid: {
                        color: '#eee'
                    }
                },
                y: {
                    ticks: {
                        color: '#333'
                    },
                    grid: {
                        color: '#eee'
                    }
                }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>