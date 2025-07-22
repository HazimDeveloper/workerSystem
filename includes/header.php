<?php
include_once 'includes/session.php';
$user = getCurrentUser();
?>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <h2>📋 Worker Document System</h2>
        </div>
        
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
            <a href="documents.php" class="nav-link">📄 Documents</a>
            <?php if (isAdmin()): ?>
                <a href="categories.php" class="nav-link">📁 Categories</a>
                <a href="users.php" class="nav-link">👥 Users</a>
            <?php endif; ?>
        </nav>
        
        <div class="user-menu">
            <span class="user-name">👤 <?php echo htmlspecialchars($user['full_name']); ?></span>
            <div class="user-dropdown">
                <a href="profile.php" class="dropdown-item">Profile</a>
                <a href="logout.php" class="dropdown-item">Logout</a>
            </div>
        </div>
    </div>
</header> 