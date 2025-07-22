<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

// Get document ID
$doc_id = $_GET['id'] ?? null;
if (!$doc_id || !is_numeric($doc_id)) {
    header('Location: documents.php');
    exit();
}

// Get document details
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->execute([$doc_id]);
$document = $stmt->fetch();

if (!$document) {
    header('Location: documents.php');
    exit();
}

// Check if file exists
if (!file_exists($document['file_path'])) {
    header('Location: documents.php');
    exit();
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $document['filename'] . '"');
header('Content-Length: ' . filesize($document['file_path']));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($document['file_path']);
exit();
?> 