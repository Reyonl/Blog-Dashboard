<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Pastikan permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];

        // Cek apakah pengguna adalah admin atau pemilik komentar
        $checkCommentStmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $checkCommentStmt->execute([$commentId]);
        $comment = $checkCommentStmt->fetch();

        if ($comment) {
            // Jika admin atau pemilik komentar, lakukan penghapusan
            if ($_SESSION['role'] === 'admin' || $comment['user_id'] === $userId) {
                // Prepare the delete statement
                $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                if ($deleteStmt->execute([$commentId])) {
                    // Redirect kembali dengan pesan sukses
                    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php") . "?message=Komentar berhasil dihapus.");
                    exit;
                } else {
                    // Handle error case
                    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php") . "?message=Gagal menghapus komentar.");
                    exit;
                }
            } else {
                // Jika bukan admin dan bukan pemilik komentar
                header("Location: user_dashboard.php?message=Anda tidak memiliki izin untuk menghapus komentar ini.");
                exit;
            }
        } else {
            // Jika komentar tidak ditemukan
            header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php") . "?message=Komentar tidak ditemukan.");
            exit;
        }
    }
} else {
    // Redirect jika diakses tanpa metode POST
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
    exit;
}
