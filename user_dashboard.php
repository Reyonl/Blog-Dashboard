<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

include 'config.php';

// Mengambil informasi pengguna
$userId = $_SESSION['user_id'];
$userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// Menyapa pengguna
$username = htmlspecialchars($user['username'] ?? 'Guest'); // Default to 'Guest' if not set

// Tambahkan counter kunjungan untuk user_dashboard
$pageName = 'user_dashboard';
$viewStmt = $pdo->prepare("SELECT view_count FROM page_views WHERE page_name = ?");
$viewStmt->execute([$pageName]);
$view = $viewStmt->fetch();

if ($view) {
    // Jika sudah ada, tambahkan view_count
    $updateViewStmt = $pdo->prepare("UPDATE page_views SET view_count = view_count + 1 WHERE page_name = ?");
    $updateViewStmt->execute([$pageName]);
} else {
    // Jika belum ada, buat record baru dengan view_count 1
    $insertViewStmt = $pdo->prepare("INSERT INTO page_views (page_name, view_count) VALUES (?, 1)");
    $insertViewStmt->execute([$pageName]);
}

// Mengambil jumlah view dari database
$totalViews = $view ? $view['view_count'] : 1; // Jika tidak ada view sebelumnya, total views adalah 1

// Mengambil semua postingan yang dipublikasikan
$postsStmt = $pdo->query("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC");
$posts = $postsStmt->fetchAll();

// Mengambil komentar untuk setiap postingan
$commentsStmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - <?php echo $username; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Gaya untuk tombol Back to Top */
        .back-to-top {
            display: none;
        }

        /* Sembunyikan kotak komentar secara default */
        .comment-box {
            display: none;
        }

        /* Gaya untuk mode gelap */
        .dark {
            background-color: #1a202c;
            color: #f7fafc;
        }

        .dark .bg-white {
            background-color: #2d3748;
            color: #f7fafc;
        }

        .dark h1, .dark h2, .dark h3, .dark h4, .dark p {
            color: #f7fafc;
        }

        /* Ubah warna teks komentar di mode gelap */
        .dark .text-gray-700 {
            color: #f7fafc;
        }
    </style>
</head>
<body class="bg-[#f5f5e9]">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-center mb-6">Hai, <?php echo $username; ?>!</h1>

        <button onclick="toggleDarkMode()" class="absolute top-6 right-6 bg-gray-800 text-white p-2 rounded">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Tombol Logout -->
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
                    $formattedDate = $createdAt->format('d M Y, H:i');
                ?>
                <p class="text-gray-500 text-sm mt-1"><em><?php echo $formattedDate; ?></em></p>

                <!-- Tombol untuk menampilkan kotak komentar -->
                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-2 comment-toggle">
                    <i class="fa-solid fa-comment"></i> Tambah Komentar
                </button>

                <!-- Kotak untuk menulis komentar -->
                <div class="comment-box mt-4">
                    <form method="POST" action="comment.php">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="content" placeholder="Tulis komentar..." required class="w-full border border-gray-300 rounded-lg p-2 mt-2" rows="3"></textarea>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-2">
                            <i class="fa-regular fa-paper-plane"></i> Kirim
                        </button>
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mt-2 cancel-comment">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </button>
                    </form>
                </div>

                <!-- Menampilkan komentar -->
                <?php
                $commentsStmt->execute([$post['id']]);
                $comments = $commentsStmt->fetchAll();
                ?>
                <h4 class="text-lg font-semibold mt-4">Komentar:</h4>
                <ul class="list-disc list-inside mt-2">
                    <?php foreach ($comments as $comment): ?>
                        <li class="text-gray-700 mb-2">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>: <?php echo htmlspecialchars($comment['content'] ?? ''); ?> - 
                            <?php
                                $commentCreatedAt = new DateTime($comment['created_at']);
                                $formattedCommentDate = $commentCreatedAt->format('d M Y, H:i');
                            ?>
                            <em class="text-gray-500"><?php echo $formattedCommentDate; ?></em>
                            <?php if ($comment['user_id'] === $userId): ?>
                                <form method="POST" action="delete_comment.php" class="inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 ml-2">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
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
                backToTopBtn.style.display = "block"; 
            } else {
                backToTopBtn.style.display = "none"; 
            }
        };

        // Fungsi untuk menggulir ke atas saat tombol diklik
        backToTopBtn.onclick = function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        };

        // Menangani klik tombol untuk menampilkan kotak komentar
        const toggleButtons = document.querySelectorAll('.comment-toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const commentBox = button.nextElementSibling;
                commentBox.style.display = commentBox.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Menangani klik tombol Batal untuk menyembunyikan kotak komentar
        const cancelButtons = document.querySelectorAll('.cancel-comment');
        cancelButtons.forEach(button => {
            button.addEventListener('click', () => {
                const commentBox = button.closest('.comment-box');
                commentBox.style.display = 'none';
            });
        });

        // Fungsi untuk mengubah mode gelap
        function toggleDarkMode() {
            document.body.classList.toggle('dark');
            const icon = document.querySelector('.fa-moon, .fa-sun');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
        }
    </script>
</body>
</html>
