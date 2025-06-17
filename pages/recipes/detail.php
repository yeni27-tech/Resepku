<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil resep
$recipe_id = (int)($_GET['id'] ?? 0);
if ($recipe_id <= 0) {
    header('Location: /resep_website_native/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT r.*, c.name as category_name, u.username 
                           FROM recipes r 
                           LEFT JOIN categories c ON r.category_id = c.id 
                           LEFT JOIN users u ON r.user_id = u.id 
                           WHERE r.id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();
    if (!$recipe) {
        header('Location: /resep_website_native/index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error mengambil resep: " . $e->getMessage();
    error_log("Error in detail.php: " . $e->getMessage());
}

// Cek apakah user sudah favorit
$user_favorited = false;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE recipe_id = ? AND user_id = ?");
        $stmt->execute([$recipe_id, $_SESSION['user_id']]);
        $user_favorited = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Error checking favorite: " . $e->getMessage());
    }
}

// Proses favorite dan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "Harus login untuk melakukan aksi ini.";
    } else {
        try {
            if ($_POST['action'] === 'favorite') {
                $stmt = $pdo->prepare("SELECT id FROM favorites WHERE recipe_id = ? AND user_id = ?");
                $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("DELETE FROM favorites WHERE recipe_id = ? AND user_id = ?");
                    $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                    $success = "Resep dihapus dari favorit.";
                    $user_favorited = false;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO favorites (recipe_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$recipe_id, $_SESSION['user_id']]);
                    $success = "Resep ditambahkan ke favorit.";
                    $user_favorited = true;
                }
            } elseif ($_POST['action'] === 'comment') {
                $content = trim($_POST['content'] ?? '');
                if (empty($content) || strlen($content) > 500) {
                    $errors[] = "Komentar harus 1-500 karakter.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)");
                    $stmt->execute([$recipe_id, $_SESSION['user_id'], $content]);
                    $success = "Komentar berhasil ditambahkan.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
            error_log("Error in detail.php: " . $e->getMessage());
        }
    }
}

// Ambil semua komentar
try {
    $stmt = $pdo->prepare("SELECT c.*, u.username 
                           FROM comments c 
                           JOIN users u ON c.user_id = u.id 
                           WHERE c.recipe_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$recipe_id]);
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching comments: " . $e->getMessage());
}

// Hitung favorit
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE recipe_id = ?");
    $stmt->execute([$recipe_id]);
    $favorite_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    $favorite_count = 0;
    error_log("Error counting favorites: " . $e->getMessage());
}

$pageTitle = htmlspecialchars($recipe['title']) . " - Resepku";
require_once '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/style.css">
<style>
    :root {
        --primary-color: #ffffff;
        --primary-dark: #f8f9fa;
        --primary-light: #ffffff;
        --secondary-color: #ff6b35;
        --secondary-light: #ff8c62;
        --secondary-dark: #e54e1b;
        --accent-color: #48bb78;
        --text-color: #2d3748;
        --text-light: #718096;
        --text-muted: #a0aec0;
        --bg-primary: #ffffff;
        --bg-secondary: #f7fafc;
        --bg-overlay: rgba(255, 255, 255, 0.95);
        --border-color: rgba(255, 107, 53, 0.1);
        --shadow-light: 0 2px 8px rgba(255, 107, 53, 0.08);
        --shadow-medium: 0 4px 16px rgba(255, 107, 53, 0.12);
        --shadow-heavy: 0 8px 32px rgba(255, 107, 53, 0.15);
        --shadow-glow: 0 0 20px rgba(255, 107, 53, 0.3);
        --gradient-orange: linear-gradient(135deg, #ff6b35 0%, #ff8c62 100%);
        --gradient-white: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        --gradient-hover: linear-gradient(135deg, #e54e1b 0%, #ff6b35 100%);
        --font-weight-light: 300;
        --font-weight-normal: 400;
        --font-weight-medium: 500;
        --font-weight-semibold: 600;
        --font-weight-bold: 700;
        --font-weight-extrabold: 800;
        --font-weight-black: 900;
        --header-height: 80px;
        --container-width: 1280px;
        --border-radius: 12px;
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;
        --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        --text-3xl: 1.875rem;
        --text-2xl: 1.5rem;
        --text-md: 1rem;
        --text-sm: 0.875rem;
    }

    body[data-theme="dark"] {
        --primary-color: #1a202c;
        --primary-dark: #2d3748;
        --primary-light: #4a5568;
        --secondary-color: #ff8c62;
        --secondary-light: #ffab8c;
        --secondary-dark: #e54e1b;
        --accent-color: #68d391;
        --text-color: #e2e8f0;
        --text-light: #a0aec0;
        --text-muted: #718096;
        --bg-primary: #2d3748;
        --bg-secondary: #1a202c;
        --bg-overlay: rgba(26, 32, 44, 0.95);
    }

    .recipe-detail {
        padding: 2rem 0;
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .recipe-image {
        flex: 0 0 400px;
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-light);
    }

    .recipe-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: rgb(253, 253, 253);
    }

    .recipe-info {
        flex: 1;
        min-width: 300px;
    }

    .recipe-info h1 {
        font-size: var(--text-3xl);
        margin-bottom: 1rem;
        color: var(--text-color);
    }

    .recipe-info p {
        color: var(--text-light);
        margin-bottom: 1rem;
    }

    .recipe-meta {
        margin-bottom: 1rem;
    }

    .recipe-meta span {
        display: inline-block;
        margin-right: 1.5rem;
        font-size: var(--text-sm);
        color: var(--text-light);
    }

    .recipe-meta i {
        margin-right: 0.5rem;
        color: var(--secondary-color);
    }

    .recipe-info .btn {
        width: 200px;
        padding: 0.75rem;
        border-radius: var(--border-radius);
        font-weight: var(--font-weight-semibold);
        transition: var(--transition-bounce);
    }

    .btn-primary {
        background: var(--gradient-orange);
        color: var(--primary-color);
    }

    .btn-primary:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: var(--shadow-medium);
    }

    .btn-outline {
        border: 2px solid var(--secondary-color);
        color: var(--secondary-color);
        background: transparent;
    }

    .btn-outline:hover {
        color: var(--primary-color);
        background: var(--gradient-orange);
        border-color: var(--secondary-dark);
    }

    .recipe-section h2 {
        font-size: var(--text-2xl);
        margin-bottom: 1rem;
        color: var(--text-color);
    }

    .recipe-section p {
        white-space: pre-wrap;
        margin-bottom: 1.5rem;
        color: var(--text-light);
    }

    .comment-form textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: var(--text-md);
        min-height: 120px;
        margin-bottom: 0.5rem;
        transition: var(--transition-fast);
    }

    .comment-form textarea:focus {
        border-color: var(--secondary-color);
        outline: none;
        box-shadow: var(--shadow-light);
    }

    .comments div {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 1rem;
    }

    .comments strong {
        color: var(--text-color);
    }

    .comments p {
        font-size: var(--text-md);
        color: var(--text-light);
    }

    .comments small {
        color: var(--text-muted);
    }

    .no-recipes {
        text-align: center;
        color: var(--text-light);
    }

    .container {
        max-width: var(--container-width);
        margin-left: auto;
        margin-right: auto;
        padding: 2rem 2rem; /* Tambahkan padding kiri dan kanan */
    }

    /* Timer Styling */
    .timer-container {
        margin-top: 1rem;
        padding: 1rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }

    #timer {
        font-size: 1.5rem;
        color: var(--secondary-color);
        font-weight: var(--font-weight-bold);
        margin-bottom: 1rem;
    }

    .timer-controls button {
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        background: var(--gradient-orange);
        color: var(--primary-color);
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .timer-controls button:hover {
        background: var(--gradient-hover);
    }

    /* Steps Styling */
    .steps-container {
        margin-top: 1rem;
        padding: 1rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }

    .step {
        display: none;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        transition: var(--transition-normal);
    }

    .step.active {
        display: block;
        background: var(--bg-secondary);
        box-shadow: var(--shadow-light);
    }

    .step-nav {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .step-nav button {
        padding: 0.5rem 1rem;
        background: var(--secondary-color);
        color: var(--primary-color);
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .step-nav button:hover {
        background: var(--secondary-light);
    }

    .step-nav button:disabled {
        background: var(--text-light);
        cursor: not-allowed;
    }
</style>

<script>
    let timeLeft = <?php echo (int)$recipe['cooking_time'] * 60; ?>;
    let timerInterval;
    let isRunning = false;

    function startTimer() {
        if (!isRunning && timeLeft > 0) {
            isRunning = true;
            timerInterval = setInterval(() => {
                if (timeLeft > 0) {
                    timeLeft--;
                    updateTimerDisplay();
                } else {
                    clearInterval(timerInterval);
                    isRunning = false;
                    alert('Waktu masak selesai!');
                }
            }, 1000);
        }
    }

    function pauseTimer() {
        if (isRunning) {
            clearInterval(timerInterval);
            isRunning = false;
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        isRunning = false;
        timeLeft = <?php echo (int)$recipe['cooking_time'] * 60; ?>;
        updateTimerDisplay();
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }

    // Langkah-langkah Real-Time
    let currentStep = 0;
    const steps = document.querySelectorAll('.step');

    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.classList.remove('active');
            if (index === stepIndex) {
                step.classList.add('active');
            }
        });
        document.getElementById('prevBtn').disabled = stepIndex === 0;
        document.getElementById('nextBtn').disabled = stepIndex === steps.length - 1;
    }

    function nextStep() {
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    }

    function prevStep() {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    }

    // Inisialisasi langkah-langkah dari instructions
    document.addEventListener('DOMContentLoaded', () => {
        updateTimerDisplay();
        const instructions = "<?php echo addslashes(str_replace("\n", '', htmlspecialchars($recipe['instructions'] ?? ''))); ?>".split('|').filter(step => step.trim() !== '');
        const stepsContainer = document.querySelector('.steps-container');
        instructions.forEach((step, index) => {
            const stepDiv = document.createElement('div');
            stepDiv.className = 'step' + (index === 0 ? ' active' : '');
            stepDiv.innerHTML = `<p>${step.trim()}</p>`;
            stepsContainer.appendChild(stepDiv);
        });
        const navDiv = document.createElement('div');
        navDiv.className = 'step-nav';
        navDiv.innerHTML = `

        `;
        stepsContainer.appendChild(navDiv);
        steps = document.querySelectorAll('.step');
    });
</script>

<main class="main-content">
    <section class="container recipe-detail">
        <?php if (isset($success)): ?>
            <p class="error-message" style="background-color: #e6ffed; color: #2e7d32; border-color: #a5d6a7;" data-toast><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error-message" data-toast><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <p class="error-message" data-toast><?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="recipe-image">
            <img src="<?php echo $recipe['image'] ? '/resep_website_native/' . htmlspecialchars($recipe['image']) : '/resep_website_native/assets/images/default-recipe.jpg'; ?>"
                alt="<?php echo htmlspecialchars($recipe['title']); ?>">
        </div>
        <div class="recipe-info">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <p><?php echo htmlspecialchars($recipe['description'] ?? ''); ?></p>
            <div class="recipe-meta">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($recipe['username'] ?: 'Anonim'); ?></span>
                <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($recipe['category_name'] ?: 'N/A'); ?></span>
                <span><i class="fas fa-clock"></i> <?php echo $recipe['cooking_time'] ? htmlspecialchars($recipe['cooking_time']) . ' menit' : 'N/A'; ?></span>
                <span><i class="fas fa-fire"></i> <?php echo $recipe['calories'] ? htmlspecialchars($recipe['calories']) . ' kcal' : 'N/A'; ?></span>
                <span><i class="fas fa-leaf"></i> <?php echo htmlspecialchars($recipe['diet_type'] ?: 'N/A'); ?></span>
            </div>
            <div class="recipe-meta">
                <span><i class="fas fa-heart"></i> <?php echo $favorite_count; ?> Favorit</span>
                <span><i class="fas fa-comment"></i> <?php echo count($comments); ?> Komentar</span>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                    <input type="hidden" name="action" value="favorite">
                    <button type="submit" class="btn <?php echo $user_favorited ? 'btn-primary' : 'btn-outline'; ?>">
                        <i class="fas fa-heart"></i> <?php echo $user_favorited ? 'Hapus Favorit' : 'Tambah Favorit'; ?>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Timer Masak Interaktif -->
            <div class="timer-container">
                <div id="timer">0:00</div>
                <div class="timer-controls">
                    <button type="button" onclick="startTimer()">Start</button>
                    <button type="button" onclick="pauseTimer()">Pause</button>
                    <button type="button" onclick="resetTimer()">Reset</button>
                </div>
            </div>
        </div>

        <div class="recipe-section">
            <h2>Bahan-bahan</h2>
            <p><?php echo htmlspecialchars($recipe['ingredients'] ?? ''); ?></p>
            <h2>Langkah-langkah</h2>
            <div class="steps-container"></div>
        </div>

        <div class="recipe-section">
            <h2>Komentar</h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST" class="comment-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                    <input type="hidden" name="action" value="comment">
                    <textarea name="content" placeholder="Tulis komentar (maks 500 karakter)..." required maxlength="500"></textarea>
                    <button type="submit" class="btn bg-orange btn-primary rounded-xl p-2">Kirim Komentar</button>
                </form>
            <?php endif; ?>
            <?php if (empty($comments)): ?>
                <p class="no-recipes">Belum ada komentar.</p>
            <?php else: ?>
                <div class="comments">
                    <?php foreach ($comments as $comment): ?>
                        <div>
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            <small><?php echo date('d M Y, H:i', strtotime($comment['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>