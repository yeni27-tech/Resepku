<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<p>ID tidak ditemukan.</p>";
    exit;
}

// Hapus relasi dari tabel lain jika ada (rating, comments, favorites, dll)
$pdo->prepare("DELETE FROM favorites WHERE recipe_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM ratings WHERE recipe_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM likes WHERE recipe_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM comments WHERE recipe_id = ?")->execute([$id]);

// Hapus resep
$stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
$stmt->execute([$id]);

header("Location: manage_recipes.php");
exit;
