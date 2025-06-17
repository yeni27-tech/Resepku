<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $category_id = (int)$_POST['category_id'];
    $diet_type = trim($_POST['diet_type']);
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/recipes/';
        $image = 'assets/images/recipes/' . uniqid() . '-' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], '../../' . $image);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO recipes (user_id, category_id, title, description, diet_type, ingredients, instructions, image, created_at) 
                               VALUES (:user_id, :category_id, :title, :description, :diet_type, :ingredients, :instructions, :image, NOW())");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'category_id' => $category_id,
            'title' => $title,
            'description' => $description,
            'diet_type' => $diet_type,
            'ingredients' => $ingredients,
            'instructions' => $instructions,
            'image' => $image
        ]);
        $success = "Resep berhasil ditambahkan!";
    } catch (PDOException $e) {
        $error = "Error menambahkan resep: " . $e->getMessage();
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Tambah Resep</h1>
    <form method="POST" enctype="multipart/form-data" class="max-w-lg mx-auto">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Judul</label>
            <input type="text" name="title" required class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Deskripsi</label>
            <textarea name="description" required class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Tipe Diet</label>
            <select name="diet_type" class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800">
                <option value="">Pilih Tipe Diet</option>
                <option value="vegan">Vegan</option>
                <option value="keto">Keto</option>
                <option value="gluten-free">Gluten-Free</option>
                <option value="vegetarian">Vegetarian</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Bahan</label>
            <textarea name="ingredients" required class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Instruksi</label>
            <textarea name="instructions" required class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Kategori</label>
            <select name="category_id" required class="w-full p-2 rounded-lg border dark:border-gray-600 dark:bg-gray-800">
                <?php
                $stmt = $pdo->query("SELECT * FROM categories");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$category['id']}'>{$category['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300">Gambar</label>
            <input type="file" name="image" accept="image/*" class="w-full p-2">
        </div>
        <button type="submit" class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Tambah Resep</button>
    </form>
    <?php if (isset($success)) echo "<p class='text-green-500 mt-4'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='text-red-500 mt-4'>$error</p>"; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>