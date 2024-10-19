<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Insert ke database
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, created_at) VALUES (?, ?, NOW())");
    if ($stmt->execute([$title, $content])) {
        header("Location: admin_dashboard.php?message=Postingan berhasil ditambahkan!");
        exit;
    } else {
        $errorMessage = "Terjadi kesalahan saat menambahkan postingan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Postingan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-[#f5f5e9]">
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
        <h1 class="text-3xl font-bold mb-6">Tambah Postingan</h1>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form action="post.php" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-sm font-semibold mb-2">Judul Postingan</label>
                <input type="text" id="title" name="title" required class="border border-gray-300 p-2 w-full rounded" placeholder="Masukkan judul..." />
            </div>

            <div class="mb-4">
                <label for="content" class="block text-sm font-semibold mb-2">Isi Postingan</label>
                <textarea id="content" name="content" required rows="10" class="border border-gray-300 p-2 w-full rounded" placeholder="Tulis isi postingan di sini..."></textarea>
            </div>

            <div class="flex justify-between mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan Postingan</button>
                <a href="admin_dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>
