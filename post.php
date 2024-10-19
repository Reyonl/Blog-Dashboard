<?php
session_start();
include 'config.php';

// Memeriksa apakah pengguna adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id']; // Ambil ID pengguna yang sedang login

    // Menyimpan postingan baru ke database
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, status, user_id) VALUES (?, ?, 'published', ?)");
    $stmt->execute([$title, $content, $user_id]);

    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Postingan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Postingan</h1>
        <form method="POST">
            <input type="text" name="title" placeholder="Judul" required>
            <textarea name="content" placeholder="Konten" required></textarea>
            <button type="submit">Tambah</button>
        </form>
    </div>
</body>
</html>