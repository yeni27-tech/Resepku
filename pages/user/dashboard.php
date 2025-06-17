<?php
include_once '../../includes/session.php';
include_once '../../includes/config.php';
include_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /resep_website_native/pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk statistik
$stmt = $pdo->prepare("
    SELECT 
        COUNT(r.id) as total_recipes,
        (SELECT COUNT(*) FROM favorites f WHERE f.user_id = :user_id) as total_favorites,
        (SELECT COUNT(*) FROM comments c WHERE c.user_id = :user_id2) as total_comments
    FROM recipes r 
    WHERE r.user_id = :user_id3 AND r.is_published = 1
");
$stmt->execute([
    ':user_id' => $user_id,
    ':user_id2' => $user_id,
    ':user_id3' => $user_id
]);
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Query untuk views dari tabel recipe_views
$stmt_views = $pdo->prepare("
    SELECT COUNT(*) as total_views 
    FROM recipe_views 
    WHERE recipe_id IN (SELECT id FROM recipes WHERE user_id = :user_id)
");
$stmt_views->execute([':user_id' => $user_id]);
$views_data = $stmt_views->fetch(PDO::FETCH_ASSOC);
$views = $views_data['total_views'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Resep Website</title>
    <link rel="stylesheet" href="../../assets/css/app.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/user/manage_recipes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Style Dashboard Dinamis */
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f4f7f9 0%, #e9ecef 100%);
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 25px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 15px;
            font-size: 2.2em;
            animation: slideDown 0.7s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        p.welcome {
            text-align: center;
            font-size: 16px;
            margin-bottom: 35px;
            color: #666;
        }

        .stats-section {
            margin-bottom: 50px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-top: 25px;
            transition: all 0.3s ease;
        }

        .stat-card {
            background: linear-gradient(145deg, #fef6e4, #fcefe2);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: linear-gradient(145deg, #fcefe2, #fad8c0);
        }

        .stat-card h3 {
            margin-bottom: 12px;
            font-size: 18px;
            color: #34495e;
            letter-spacing: 1px;
        }

        .stat-card p {
            font-size: 26px;
            font-weight: 700;
            color: #1e88e5;
            margin: 0;
        }

        .chart-container {
            margin-top: 30px;
            height: 350px;
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-nav {
            margin: 50px 0;
        }

        .dashboard-nav h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .dashboard-nav ul {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .dashboard-nav li {
            background: #ffffff;
            padding: 18px 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease-in-out;
        }

        .dashboard-nav li:hover {
            background: #fef9f5;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        }

        .dashboard-nav a {
            text-decoration: none;
            color: #34495e;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.2s ease;
        }

        .dashboard-nav a:hover {
            color: #1e88e5;
        }

        .history-section {
            background: #fafafa;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #eee;
            animation: fadeIn 0.5s ease-in;
        }

        .history-section p {
            font-style: italic;
            color: #888;
            font-size: 15px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 15px;
            }

            .stats-grid,
            .dashboard-nav ul {
                grid-template-columns: 1fr;
            }

            .stat-card p {
                font-size: 22px;
            }

            h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['email']); ?>!</h1>
        <p class="welcome">Kelola aktivitas dan resep Anda di sini. Terakhir diperbarui: <?php echo date('H:i, d M Y', time()); ?></p>

        <!-- Statistik Aktivitas -->
        <section class="stats-section">
            <h2>Statistik Aktivitas Anda</h2>
            <div class="stats-grid">
                <div class="stat-card" onclick="alert('Lihat detail resep Anda!')">
                    <h3>Total Resep</h3>
                    <p><?php echo $user_stats['total_recipes']; ?></p>
                </div>
                <div class="stat-card" onclick="alert('Lihat daftar favorit Anda!')">
                    <h3>Resep Favorit</h3>
                    <p><?php echo $user_stats['total_favorites']; ?></p>
                </div>
                <div class="stat-card" onclick="alert('Lihat statistik views!')">
                    <h3>Views Resep</h3>
                    <p><?php echo $views; ?></p>
                </div>
                <div class="stat-card" onclick="alert('Lihat komentar Anda!')">
                    <h3>Komentar yang Dikeluarkan</h3>
                    <p><?php echo $user_stats['total_comments']; ?></p>
                </div>
            </div>
            <!-- Chart untuk Statistik -->
            <div id="statsChart" class="chart-container"></div>
        </section>

        <!-- Navigasi Menu -->
        <section class="dashboard-nav">
            <h2>Menu Dashboard</h2>
            <ul>
                <li><a href="/resep_website_native/pages/user/manage_recipes.php">Kelola Resep Saya</a></li>
                <li><a href="/resep_website_native/pages/user/favorites.php">Resep Favorit</a></li>
                <li><a href="/resep_website_native/pages/recipes/add.php">Tambah Resep Baru</a></li>
                <li><a href="/resep_website_native/pages/user/edit_profile.php">Edit Profil</a></li>
                <li><a href="/resep_website_native/pages/user/preferences.php">Atur Preferensi</a></li>
                <li><a href="/resep_website_native/pages/user/profile.php">Lihat Profil</a></li>
                <li><a href="/resep_website_native/pages/recipes/detail.php?id=1">Lihat Resep (Contoh)</a></li>
            </ul>
        </section>

        <!-- Histori Aktivitas (Placeholder) -->
        <section class="history-section">
            <h2>Riwayat Aktivitas</h2>
            <p>Belum ada data riwayat. Akan diperbarui segera.</p>
        </section>
    </div>

    <?php include_once '../../includes/footer.php'; ?>

    <!-- Chart JS Config -->
    <script type="chartjs">
        {
        "type": "bar",
        "data": {
            "labels": ["Total Resep", "Favorit", "Views", "Komentar"],
            "datasets": [{
                "label": "Statistik",
                "data": [
                    <?php echo $user_stats['total_recipes']; ?>,
                    <?php echo $user_stats['total_favorites']; ?>,
                    <?php echo $views; ?>,
                    <?php echo $user_stats['total_comments']; ?>
                ],
                "backgroundColor": ["#4CAF50", "#FF9800", "#2196F3", "#9C27B0"],
                "borderColor": ["#388E3C", "#F57C00", "#1976D2", "#7B1FA2"],
                "borderWidth": 1,
                "borderRadius": 5
            }]
        },
        "options": {
            "responsive": true,
            "maintainAspectRatio": false,
            "scales": {
                "y": {
                    "beginAtZero": true,
                    "ticks": { "color": "#34495e" }
                },
                "x": {
                    "ticks": { "color": "#34495e" }
                }
            },
            "plugins": {
                "legend": {
                    "position": "top",
                    "labels": { "color": "#34495e" }
                },
                "title": {
                    "display": true,
                    "text": "Statistik Aktivitas Anda",
                    "color": "#2c3e50",
                    "font": { "size": 18 }
                },
                "tooltip": {
                    "backgroundColor": "#ffffff",
                    "titleColor": "#2c3e50",
                    "bodyColor": "#34495e",
                    "borderColor": "#ddd",
                    "borderWidth": 1
                }
            },
            "animation": {
                "duration": 1000,
                "easing": "easeInOutQuad"
            }
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>