<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();
$message = '';
$error = '';

// Get categories for dropdown/filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Handle add document POST
if (isset($_GET['add']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'] ?: null;
    
    if (empty($name)) {
        $error = 'Document name is required.';
    } elseif (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        $file = $_FILES['document'];
        $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $error = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, JPG, PNG, GIF';
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            $error = 'File size too large. Maximum 10MB allowed.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . $file['name'];
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO documents (name, description, filename, file_path, file_size, file_type, category_id, uploaded_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $name, 
                        $description, 
                        $file['name'], 
                        $file_path, 
                        $file['size'], 
                        $file_extension, 
                        $category_id, 
                        $user['id']
                    ]);
                    
                    $message = 'Document uploaded successfully!';
                    // Clear form fields
                    $_POST = [];
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    // Delete uploaded file if database insert fails
                    unlink($file_path);
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $doc_id = $_GET['delete'];
    
    // Check if user can delete this document
    $stmt = $pdo->prepare("SELECT uploaded_by FROM documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $document = $stmt->fetch();
    
    if ($document && (isAdmin() || $document['uploaded_by'] == $user['id'])) {
        // Get file path before deleting
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->execute([$doc_id]);
        $file_path = $stmt->fetchColumn();
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
        if ($stmt->execute([$doc_id])) {
            // Delete physical file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $message = 'Document deleted successfully!';
        } else {
            $error = 'Failed to delete document.';
        }
    } else {
        $error = 'You do not have permission to delete this document.';
    }
}

// Search and filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(d.name LIKE ? OR d.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "d.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get documents
$query = "
    SELECT d.*, c.name as category_name, u.full_name as uploaded_by_name 
    FROM documents d 
    LEFT JOIN categories c ON d.category_id = c.id 
    LEFT JOIN users u ON d.uploaded_by = u.id 
    $where_clause
    ORDER BY d.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$documents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - Worker Document System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📄 Documents</h1>
            <a href="documents.php?add=1" class="btn btn-primary">➕ Add Document</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['add'])): ?>
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="form">
                    <div class="form-group">
                        <label for="name">Document Name *:</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="document">File *:</label>
                        <input type="file" id="document" name="document" required accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif">
                        <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, PNG, GIF (Max 10MB)</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Upload Document</button>
                        <a href="documents.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
        <!-- Search and Filter -->
        <div class="search-filter">
            <form method="GET" class="search-form">
                <div class="search-inputs">
                    <input type="text" name="search" placeholder="Search documents..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">🔍 Search</button>
                    <a href="documents.php" class="btn btn-outline">Clear</a>
                </div>
            </form>
        </div>
        
        <!-- Documents List -->
        <div class="documents-container">
            <?php if (empty($documents)): ?>
                <div class="no-data">
                    <p>No documents found.</p>
                    <a href="documents.php?add=1" class="btn btn-primary">Upload your first document</a>
                </div>
            <?php else: ?>
                <div class="documents-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Category</th>
                                <th>Uploaded By</th>
                                <th>Size</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <div class="doc-info">
                                            <div class="doc-icon">📄</div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($doc['name']); ?></h4>
                                                <p><?php echo htmlspecialchars($doc['description']); ?></p>
                                                <small><?php echo htmlspecialchars($doc['filename']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($doc['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['uploaded_by_name']); ?></td>
                                    <td><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB</td>
                                    <td><?php echo date('M j, Y', strtotime($doc['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="download.php?id=<?php echo $doc['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Download">⬇️</a>
                                            
                                            <?php if (isAdmin() || $doc['uploaded_by'] == $user['id']): ?>
                                                <a href="edit_document.php?id=<?php echo $doc['id']; ?>" 
                                                   class="btn btn-sm btn-secondary" title="Edit">✏️</a>
                                                
                                                <a href="?delete=<?php echo $doc['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this document?')">🗑️</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 