<?php
require_once '../../includes/session.php';
require_once '../../includes/config.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /resep_website_native/pages/login.php');
    exit;
}

$success = '';
$error = '';
$users_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $users_per_page;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user_id'])) {
        $delete_user_id = (int)$_POST['delete_user_id'];
        if ($delete_user_id !== $_SESSION['user_id']) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute(['id' => $delete_user_id]);
                $success = "Pengguna berhasil dihapus!";
            } catch (PDOException $e) {
                $error = "Gagal menghapus pengguna: " . $e->getMessage();
                if ($e->getCode() === '23000') {
                    $error = "Pengguna tidak dapat dihapus karena terkait dengan data lain!";
                }
            }
        } else {
            $error = "Anda tidak dapat menghapus akun Anda sendiri!";
        }
    } elseif (isset($_POST['update_role'])) {
        $user_id = (int)$_POST['user_id'];
        $new_role = $_POST['role'];
        if ($user_id !== $_SESSION['user_id'] && in_array($new_role, ['user', 'admin'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
                $stmt->execute(['role' => $new_role, 'id' => $user_id]);
                $success = "Role pengguna berhasil diperbarui!";
            } catch (PDOException $e) {
                $error = "Error memperbarui role: " . $e->getMessage();
            }
        } else {
            $error = "Anda tidak dapat mengubah role akun Anda sendiri atau role tidak valid!";
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();
    $total_pages = ceil($total_users / $users_per_page);

    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users LIMIT :offset, :limit");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $users_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error mengambil daftar pengguna: " . $e->getMessage();
}
?>

<div class="container">
    <div class="manage-users">
        <h1 class="title">Kelola Pengguna</h1>
        <div class="card">
            <?php if ($success): ?>
                <p class="message success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="message error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" class="role-select">
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($user['created_at']))); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                    <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="/resep_website_native/pages/admin/manage_users.php?page=<?php echo $i; ?>" class="pagination-link<?php echo $i === $page ? ' active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

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
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background-color: var(--bg-secondary);
    }

    .manage-users {
        text-align: center;
    }

    .title {
        font-size: 2rem;
        font-weight: bold;
        color: var(--text-color);
        margin-bottom: 1.5rem;
    }

    .card {
        background: var(--primary-color);
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        border: 1px solid var(--text-muted);
        overflow: hidden;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .users-table th,
    .users-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--text-muted);
    }

    .users-table th {
        background-color: var(--secondary-color);
        color: var(--primary-color);
    }

    .role-select {
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid var(--text-muted);
        background: var(--primary-light);
        color: var(--text-color);
        cursor: pointer;
    }

    .role-select:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 5px rgba(72, 187, 120, 0.5);
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.1s ease;
        width: auto;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-danger {
        background-color: #dc3545;
        color: var(--primary-color);
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .message {
        margin-top: 1.5rem;
        padding: 0.75rem;
        border-radius: 5px;
        text-align: center;
        width: 100%;
        box-sizing: border-box;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .pagination {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .pagination-link {
        padding: 0.5rem 1rem;
        border: 1px solid var(--text-muted);
        border-radius: 5px;
        text-decoration: none;
        color: var(--text-color);
        background: var(--primary-light);
    }

    .pagination-link.active {
        background-color: var(--secondary-color);
        color: var(--primary-color);
        border-color: var(--secondary-color);
    }

    .pagination-link:hover {
        background-color: var(--secondary-light);
        color: var(--primary-color);
    }

    @media (max-width: 600px) {
        .users-table {
            display: block;
            overflow-x: auto;
        }

        .pagination {
            flex-wrap: wrap;
        }
    }
</style>

<?php require_once '../../includes/footer.php'; ?>