<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /resep_website_native/pages/user/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $recipe_id = (int)$_POST['recipe_id'];
    try {
        // Ambil path gambar untuk dihapus
        $stmt = $pdo->prepare("SELECT image FROM recipes WHERE id = ? AND user_id = ?");
        $stmt->execute([$recipe_id, $_SESSION['user_id']]);
        $recipe = $stmt->fetch();

        if ($recipe) {
            // Hapus resep
            $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
            $stmt->execute([$recipe_id, $_SESSION['user_id']]);

            // Hapus gambar jika ada
            if ($recipe['image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $recipe['image'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/resep_website_native/' . $recipe['image']);
            }

            $_SESSION['success'] = "Resep berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Resep tidak ditemukan atau Anda tidak memiliki izin.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error menghapus resep: " . $e->getMessage();
        error_log("Error in delete.php: " . $e->getMessage());
    }
}

header('Location: /resep_website_native/pages/user/manage_recipes.php');
exit;
