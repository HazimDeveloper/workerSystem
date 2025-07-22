<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.3); justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; padding: 30px 25px 20px 25px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); min-width: 320px; max-width: 95vw; position: relative; }
        .modal-close { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; color: #718096; background: none; border: none; cursor: pointer; }
    </style>
</head>
<body>
<?php
include 'includes/session.php';
include 'includes/header.php';
include 'config/database.php';

if (!isAdmin()) {
    die('<div class="alert alert-danger">Access denied. Admins only.</div>');
}

$message = '';
$error = '';

// Handle Add User
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user';
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'All fields except role are required.';
    } else {
        // Check for duplicate username/email
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)');
            if ($stmt->execute([$username, $hashed, $email, $full_name, $role])) {
                $message = 'User added successfully!';
            } else {
                $error = 'Failed to add user.';
            }
        }
    }
}

// Handle Edit User
if (isset($_POST['edit_user'])) {
    $id = $_POST['edit_id'];
    $username = trim($_POST['edit_username']);
    $email = trim($_POST['edit_email']);
    $full_name = trim($_POST['edit_full_name']);
    $role = $_POST['edit_role'] ?? 'user';
    $password = $_POST['edit_password'];
    if (empty($username) || empty($email) || empty($full_name)) {
        $error = 'Username, email, and full name are required.';
    } else {
        // Check for duplicate username/email (excluding self)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?');
        $stmt->execute([$username, $email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, full_name=?, role=?, password=? WHERE id=?');
                $success = $stmt->execute([$username, $email, $full_name, $role, $hashed, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, full_name=?, role=? WHERE id=?');
                $success = $stmt->execute([$username, $email, $full_name, $role, $id]);
            }
            if ($success) {
                $message = 'User updated successfully!';
            } else {
                $error = 'Failed to update user.';
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    // Prevent deleting self
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
        $error = 'You cannot delete your own account.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        if ($stmt->execute([$id])) {
            $message = 'User deleted successfully!';
        } else {
            $error = 'Failed to delete user.';
        }
    }
}

// Fetch users from the database using PDO
$sql = "SELECT * FROM users ORDER BY id ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.3); justify-content: center; align-items: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; padding: 30px 25px 20px 25px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); min-width: 320px; max-width: 95vw; position: relative; }
        .modal-close { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; color: #718096; background: none; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="page-header">
    <h1>User Management</h1>
    <button class="btn btn-primary" id="openAddUserModal">Add User</button>
</div>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="documents-container">
    <div class="documents-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach($users as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><span class="role-badge"><?php echo htmlspecialchars($row['role']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-warning btn-sm editUserBtn" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-full_name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                    data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                >Edit</button>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="no-data">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Add User Modal -->
<div class="modal" id="addUserModal">
    <div class="modal-content">
        <button class="modal-close" id="closeAddUserModal" title="Close">&times;</button>
        <h2>Add User</h2>
        <form method="POST" class="form" autocomplete="off">
            <input type="hidden" name="add_user" value="1">
            <div class="form-group">
                <label for="username">Username*:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email*:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name*:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="password">Password*:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>
<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <button class="modal-close" id="closeEditUserModal" title="Close">&times;</button>
        <h2>Edit User</h2>
        <form method="POST" class="form" autocomplete="off">
            <input type="hidden" name="edit_user" value="1">
            <input type="hidden" id="edit_id" name="edit_id">
            <div class="form-group">
                <label for="edit_username">Username*:</label>
                <input type="text" id="edit_username" name="edit_username" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email*:</label>
                <input type="email" id="edit_email" name="edit_email" required>
            </div>
            <div class="form-group">
                <label for="edit_full_name">Full Name*:</label>
                <input type="text" id="edit_full_name" name="edit_full_name" required>
            </div>
            <div class="form-group">
                <label for="edit_password">Password:</label>
                <input type="password" id="edit_password" name="edit_password" placeholder="Leave blank to keep current password">
            </div>
            <div class="form-group">
                <label for="edit_role">Role:</label>
                <select id="edit_role" name="edit_role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
<script>
    // Add User Modal
    const openAddBtn = document.getElementById('openAddUserModal');
    const closeAddBtn = document.getElementById('closeAddUserModal');
    const addModal = document.getElementById('addUserModal');
    if (openAddBtn && closeAddBtn && addModal) {
        openAddBtn.onclick = function() { addModal.classList.add('active'); };
        closeAddBtn.onclick = function() { addModal.classList.remove('active'); };
        window.onclick = function(event) {
            if (event.target === addModal) { addModal.classList.remove('active'); }
        };
    }
    // Edit User Modal
    const editBtns = document.querySelectorAll('.editUserBtn');
    const editModal = document.getElementById('editUserModal');
    const closeEditBtn = document.getElementById('closeEditUserModal');
    if (editBtns && editModal && closeEditBtn) {
        editBtns.forEach(btn => {
            btn.onclick = function() {
                document.getElementById('edit_id').value = btn.getAttribute('data-id');
                document.getElementById('edit_username').value = btn.getAttribute('data-username');
                document.getElementById('edit_email').value = btn.getAttribute('data-email');
                document.getElementById('edit_full_name').value = btn.getAttribute('data-full_name');
                document.getElementById('edit_role').value = btn.getAttribute('data-role');
                document.getElementById('edit_password').value = '';
                editModal.classList.add('active');
            };
        });
        closeEditBtn.onclick = function() { editModal.classList.remove('active'); };
        window.onclick = function(event) {
            if (event.target === editModal) { editModal.classList.remove('active'); }
        };
    }
    // Auto-open modals if there was a POST (validation error)
    <?php if (isset($_POST['add_user']) && $error): ?>
    addModal.classList.add('active');
    <?php endif; ?>
    <?php if (isset($_POST['edit_user']) && $error): ?>
    editModal.classList.add('active');
    <?php endif; ?>
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>
