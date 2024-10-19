<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];

        // Prepare the delete statement
        $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        if ($deleteStmt->execute([$commentId])) {
            // Redirect back to the admin dashboard with a success message
            header("Location: admin_dashboard.php?message=Komentar berhasil dihapus.");
            exit;
        } else {
            // Handle error case
            header("Location: admin_dashboard.php?message=Gagal menghapus komentar.");
            exit;
        }
    }
} else {
    // Redirect if accessed without POST method
    header("Location: admin_dashboard.php");
    exit;
}
