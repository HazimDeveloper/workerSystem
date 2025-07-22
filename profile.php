<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();

// Get user's document count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE uploaded_by = ?");
$stmt->execute([$user['id']]);
$doc_count = $stmt->fetchColumn();

// Get user's recent documents
$stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name 
    FROM documents d 
    LEFT JOIN categories c ON d.category_id = c.id 
    WHERE d.uploaded_by = ? 
    ORDER BY d.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recent_docs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Worker Document System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>👤 User Profile</h1>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">👤</div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $doc_count; ?></span>
                        <span class="stat-label">Documents Uploaded</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-content">
                <h3>📄 Recent Documents</h3>
                <?php if (empty($recent_docs)): ?>
                    <div class="no-data">
                        <p>No documents uploaded yet.</p>
                        <a href="add_document.php" class="btn btn-primary">Upload Your First Document</a>
                    </div>
                <?php else: ?>
                    <div class="document-list">
                        <?php foreach ($recent_docs as $doc): ?>
                            <div class="document-item">
                                <div class="doc-icon">📄</div>
                                <div class="doc-info">
                                    <h4><?php echo htmlspecialchars($doc['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($doc['description']); ?></p>
                                    <small>
                                        Category: <?php echo htmlspecialchars($doc['category_name'] ?? 'Uncategorized'); ?> | 
                                        <?php echo date('M j, Y', strtotime($doc['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="doc-actions">
                                    <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary">Download</a>
                                    <a href="edit_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
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