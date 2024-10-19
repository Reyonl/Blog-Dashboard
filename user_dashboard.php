<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

include 'config.php';

// Mengambil semua postingan yang dipublikasikan
$postsStmt = $pdo->query("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC");
$posts = $postsStmt->fetchAll();

// Mengambil komentar untuk setiap postingan
$commentsStmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Gaya untuk tombol Back to Top */
        .back-to-top {
            display: none; /* Mulai dengan menyembunyikan tombol */
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-center mb-6">User Dashboard</h1>
        
        <!-- Tombol Logout dipindahkan ke atas -->
        <p class="text-center mb-4">
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
        </p>

        <h2 class="text-2xl font-semibold mb-4">Postingan Terbaru</h2>
        
        <?php foreach ($posts as $post): ?>
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($post['content']); ?></p>
                
                <!-- Mengformat timestamp -->
                <?php
                    $createdAt = new DateTime($post['created_at']);
                    $formattedDate = $createdAt->format('d M Y, H:i'); // Format tanggal
                ?>
                <p class="text-gray-500 text-sm mt-1"><em><?php echo $formattedDate; ?></em></p>

                <!-- Form untuk menambahkan komentar -->
                <form method="POST" action="comment.php" class="mt-4">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <textarea name="content" placeholder="Tulis komentar..." required class="w-full border border-gray-300 rounded-lg p-2 mt-2" rows="3"></textarea>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-2">Kirim Komentar</button>
                </form>

                <!-- Menampilkan komentar -->
                <?php
                $commentsStmt->execute([$post['id']]);
                $comments = $commentsStmt->fetchAll();
                ?>
                <h4 class="text-lg font-semibold mt-4">Komentar:</h4>
                <ul class="list-disc list-inside mt-2">
                    <?php foreach ($comments as $comment): ?>
                        <li class="text-gray-700 mb-2">
                            <?php echo htmlspecialchars($comment['content']); ?> - 
                            <?php
                                $commentCreatedAt = new DateTime($comment['created_at']);
                                $formattedCommentDate = $commentCreatedAt->format('d M Y, H:i');
                            ?>
                            <em class="text-gray-500"><?php echo $formattedCommentDate; ?></em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <!-- Tombol Back to Top -->
        <button class="back-to-top fixed bottom-4 right-4 bg-blue-500 text-white rounded-full p-2 shadow hover:bg-blue-600" id="backToTopBtn">
            ↑
        </button>
    </div>

    <script>
        // Menampilkan atau menyembunyikan tombol Back to Top
        const backToTopBtn = document.getElementById('backToTopBtn');

        window.onscroll = function() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                backToTopBtn.style.display = "block"; // Tampilkan tombol
            } else {
                backToTopBtn.style.display = "none"; // Sembunyikan tombol
            }
        };

        // Fungsi untuk menggulir ke atas saat tombol diklik
        backToTopBtn.onclick = function() {
            window.scrollTo({top: 0, behavior: 'smooth'}); // Gulir halus ke atas
        };
    </script>
</body>
</html>
