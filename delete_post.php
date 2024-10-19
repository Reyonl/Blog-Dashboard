<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];

    // Prepare the SQL statement to delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$postId]);

    header("Location: admin_dashboard.php?message=Postingan berhasil dihapus.");
    exit;
} else {
    header("Location: admin_dashboard.php?message=Terjadi kesalahan saat menghapus postingan.");
    exit;
}
