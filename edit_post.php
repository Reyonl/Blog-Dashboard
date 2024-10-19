<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Mengambil ID postingan dari URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    // Mengambil data postingan dari database
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    // Jika postingan tidak ditemukan
    if (!$post) {
        echo "Postingan tidak ditemukan.";
        exit;
    }
} else {
    echo "ID postingan tidak ditentukan.";
    exit;
}

// Proses pembaruan data setelah formulir disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Memperbarui data postingan di database
    $updateStmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $updateStmt->execute([$title, $content, $postId]);

    // Redirect ke dashboard setelah berhasil
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Postingan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
        <h1 class="text-3xl font-bold mb-6">Edit Postingan</h1>

        <form method="POST" action="">
            <div class="mb-4">
 <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Konten:</label>
                <textarea name="content" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>