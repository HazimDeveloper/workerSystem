<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();
$message = '';
$error = '';

// Get document ID
$doc_id = $_GET['id'] ?? null;
if (!$doc_id || !is_numeric($doc_id)) {
    header('Location: documents.php');
    exit();
}

// Get document details
$stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name 
    FROM documents d 
    LEFT JOIN categories c ON d.category_id = c.id 
    WHERE d.id = ?
");
$stmt->execute([$doc_id]);
$document = $stmt->fetch();

if (!$document) {
    header('Location: documents.php');
    exit();
}

// Check permissions
if (!isAdmin() && $document['uploaded_by'] != $user['id']) {
    header('Location: documents.php');
    exit();
}

// Get categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'] ?: null;
    
    if (empty($name)) {
        $error = 'Document name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE documents 
                SET name = ?, description = ?, category_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $category_id, $doc_id]);
            
            $message = 'Document updated successfully!';
            
            // Refresh document data
            $stmt = $pdo->prepare("
                SELECT d.*, c.name as category_name 
                FROM documents d 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.id = ?
            ");
            $stmt->execute([$doc_id]);
            $document = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - Worker Document System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>✏️ Edit Document</h1>
            <a href="documents.php" class="btn btn-secondary">← Back to Documents</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="name">Document Name *:</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($document['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($document['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $document['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Current File:</label>
                    <div class="current-file">
                        <span class="file-icon">📄</span>
                        <span class="file-name"><?php echo htmlspecialchars($document['filename']); ?></span>
                        <span class="file-size">(<?php echo number_format($document['file_size'] / 1024, 1); ?> KB)</span>
                    </div>
                    <small>File cannot be changed. Upload a new document if needed.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Document</button>
                    <a href="documents.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 