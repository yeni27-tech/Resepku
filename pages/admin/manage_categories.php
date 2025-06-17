<?php
include_once '../../includes/session.php';
include_once '../../includes/config.php';
include_once '../../includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /resep_website_native/pages/login.php");
    exit();
}

// Proses CRUD
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
            $message = "Kategori berhasil ditambahkan!";
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $name, ':id' => $id]);
            $message = "Kategori berhasil diperbarui!";
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message = "Kategori berhasil dihapus!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Kode error untuk foreign key constraint
                $message = "Kategori tidak dapat dihapus karena sedang digunakan pada resep!";
            } else {
                $message = "Terjadi kesalahan saat menghapus kategori.";
            }
        }    
}
}

// Ambil data kategori
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Resep Website</title>
    <link rel="stylesheet" href="../../assets/css/app.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f4f7f9 0%, #e9ecef 100%);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            color: #27ae60;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            background:rgb(255, 137, 34);
            color: #fff;
            transition: background 0.3s;
        }

        .btn:hover {
            background:rgb(255, 160, 44);
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f5f6fa;
            color: #2c3e50;
        }

        tr:hover {
            background: #f9f9f9;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 15px;
            }

            table {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Kelola Kategori</h1>
        <?php if ($message) echo "<div class='message'>$message</div>"; ?>

        <!-- Form Tambah Kategori -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Kategori Baru:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" name="add" class="btn">Tambah Kategori</button>
        </form>

        <!-- Tabel Kategori -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['id']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required style="width:150px;">
                                <button type="submit" name="edit" class="btn">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="" onsubmit="return confirm('Yakin ingin menghapus?');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                                <button type="submit" name="delete" class="btn btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>