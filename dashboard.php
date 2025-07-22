<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();

// Get document statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_docs FROM documents");
$totalDocs = $stmt->fetch()['total_docs'];

$stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
$totalCategories = $stmt->fetch()['total_categories'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch()['total_users'];

// Get recent documents
$stmt = $pdo->query("
    SELECT d.*, c.name as category_name, u.full_name as uploaded_by_name 
    FROM documents d 
    LEFT JOIN categories c ON d.category_id = c.id 
    LEFT JOIN users u ON d.uploaded_by = u.id 
    ORDER BY d.created_at DESC 
    LIMIT 5
");
$recentDocs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Worker Document System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>📊 Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-content">
                    <h3><?php echo $totalDocs; ?></h3>
                    <p>Total Documents</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-content">
                    <h3><?php echo $totalCategories; ?></h3>
                    <p>Categories</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Users</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="recent-documents">
                <h2>📋 Recent Documents</h2>
                <?php if (empty($recentDocs)): ?>
                    <p class="no-data">No documents uploaded yet.</p>
                <?php else: ?>
                    <div class="document-list">
                        <?php foreach ($recentDocs as $doc): ?>
                            <div class="document-item">
                                <div class="doc-icon">📄</div>
                                <div class="doc-info">
                                    <h4><?php echo htmlspecialchars($doc['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($doc['description']); ?></p>
                                    <small>
                                        Category: <?php echo htmlspecialchars($doc['category_name'] ?? 'Uncategorized'); ?> | 
                                        Uploaded by: <?php echo htmlspecialchars($doc['uploaded_by_name']); ?> | 
                                        <?php echo date('M j, Y', strtotime($doc['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="doc-actions">
                                    <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary">Download</a>
                                    <?php if (isAdmin() || $doc['uploaded_by'] == $user['id']): ?>
                                        <a href="edit_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 