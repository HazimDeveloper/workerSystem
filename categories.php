<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireAdmin();

$message = '';
$error = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $cat_id = $_GET['delete'];
    
    // Check if category is in use
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE category_id = ?");
    $stmt->execute([$cat_id]);
    $doc_count = $stmt->fetchColumn();
    
    if ($doc_count > 0) {
        $error = 'Cannot delete category that has documents.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$cat_id])) {
            $message = 'Category deleted successfully!';
        } else {
            $error = 'Failed to delete category.';
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $message = 'Category added successfully!';
            $name = $description = '';
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get categories
$stmt = $pdo->query("
    SELECT c.*, COUNT(d.id) as doc_count 
    FROM categories c 
    LEFT JOIN documents d ON c.id = d.category_id 
    GROUP BY c.id 
    ORDER BY c.name
");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Worker Document System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            overflow: auto;
            background: rgba(0,0,0,0.3);
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            padding: 30px 25px 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            min-width: 320px;
            max-width: 95vw;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            color: #718096;
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📁 Categories</h1>
            <button class="btn btn-primary" id="openAddCategoryModal">➕ Add Category</button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="categories-container">
            <!-- Add Category Modal -->
            <div class="modal" id="addCategoryModal">
                <div class="modal-content">
                    <button class="modal-close" id="closeAddCategoryModal" title="Close">&times;</button>
                    <h2>➕ Add New Category</h2>
                    <form method="POST" class="form" autocomplete="off">
                        <div class="form-group">
                            <label for="name">Category Name *:</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Categories List -->
            <div class="categories-list">
                <h2>📋 All Categories</h2>
                <?php if (empty($categories)): ?>
                    <div class="no-data">
                        <p>No categories found.</p>
                    </div>
                <?php else: ?>
                    <div class="categories-grid">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <div class="category-header">
                                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <div class="category-actions">
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this category?')">🗑️</a>
                                    </div>
                                </div>
                                <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                <div class="category-stats">
                                    <span class="doc-count"><?php echo $category['doc_count']; ?> documents</span>
                                    <span class="created-date">Created: <?php echo date('M j, Y', strtotime($category['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Modal open/close logic
        const openBtn = document.getElementById('openAddCategoryModal');
        const closeBtn = document.getElementById('closeAddCategoryModal');
        const modal = document.getElementById('addCategoryModal');
        if (openBtn && closeBtn && modal) {
            openBtn.onclick = function() {
                modal.classList.add('active');
            };
            closeBtn.onclick = function() {
                modal.classList.remove('active');
            };
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            };
        }
        // Auto-open modal if there was a POST (validation error or just added)
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' || $error): ?>
        modal.classList.add('active');
        <?php endif; ?>
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html> 