<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Menghitung jumlah postingan dalam satu bulan terakhir
$postCountStmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE created_at >= NOW() - INTERVAL 1 MONTH");
$postCount = $postCountStmt->fetchColumn();

// Menghitung jumlah komentar dalam satu bulan terakhir
$commentCountStmt = $pdo->query("SELECT COUNT(*) as count FROM comments WHERE created_at >= NOW() - INTERVAL 1 MONTH");
$commentCount = $commentCountStmt->fetchColumn();

// Menghitung jumlah pengunjung dalam satu bulan terakhir
$visitorCountStmt = $pdo->query("SELECT COUNT(*) as count FROM visitors WHERE visit_date >= NOW() - INTERVAL 1 MONTH");
$visitorCount = $visitorCountStmt->fetchColumn();

// Mengambil semua postingan
$postsStmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $postsStmt->fetchAll();

// Mengambil komentar untuk setiap postingan dengan nama pengguna
$commentsStmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY comments.created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function confirmDeletePost() {
            return confirm("Apakah Anda yakin ingin menghapus postingan ini?");
        }

        function confirmDeleteComment() {
            return confirm("Apakah Anda yakin ingin menghapus komentar ini?");
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-4">Statistik Bulan Ini</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-blue-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Jumlah Postingan</h3>
                <p class="text-2xl font-semibold"><?php echo $postCount; ?></p>
            </div>
            <div class="p-4 bg-green-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Jumlah Komentar</h3>
                <p class="text-2xl font-semibold"><?php echo $commentCount; ?></p>
            </div>
            <div class="p-4 bg-yellow-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Jumlah Pengunjung</h3>
                <p class="text-2xl font-semibold"><?php echo $visitorCount; ?></p>
            </div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Postingan Terbaru</h2>
        <?php foreach ($posts as $post): ?>
            <div class="mb-6 p-4 bg-gray-50 rounded-lg shadow">
                <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($post['content']); ?></p>

                <!-- Mengformat timestamp -->
                <?php
                    $createdAt = new DateTime($post['created_at']);
                    $formattedDate = $createdAt->format('d M Y, H:i'); // Format tanggal
                ?>
                <p class="text-gray-500 text-sm mt-1"><em><?php echo $formattedDate; ?></em></p>

                <!-- Tautan Edit -->
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:underline">Edit Postingan</a>

                <!-- Delete Button -->
                <form action="delete_post.php" method="POST" class="inline" onsubmit="return confirmDeletePost();">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="text-red-500 hover:underline">Hapus Postingan</button>
                </form>

                <!-- Menampilkan komentar -->
                <?php
                $commentsStmt->execute([$post['id']]);
                $comments = $commentsStmt->fetchAll();
                ?>
                <h4 class="text-lg font-semibold mt-4">Komentar:</h4>
                <ul class="list-disc list-inside mt-2">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <li class="mb-2">
                                <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> 
                                <?php echo htmlspecialchars($comment['content']); ?> - 
                                <?php
                                    $commentCreatedAt = new DateTime($comment['created_at']);
                                    $formattedCommentDate = $commentCreatedAt->format('d M Y, H:i');
                                ?>
                                <em class="text-gray-500"><?php echo $formattedCommentDate; ?></em>

                                <!-- Delete Button for Comment -->
                                <form action="delete_comment.php" method="POST" class="inline" onsubmit="return confirmDeleteComment();">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Tidak ada komentar untuk postingan ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endforeach; ?>
        
        <div class="flex justify-between mt-4">
            <a href="post.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Tambah Postingan</a>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
        </div>
    </div>
</body>
</html>
