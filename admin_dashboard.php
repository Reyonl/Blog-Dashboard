<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
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

// Menambahkan view ke halaman dashboard
$pageName = 'admin_dashboard';
$viewStmt = $pdo->prepare("SELECT view_count FROM page_views WHERE page_name = ?");
$viewStmt->execute([$pageName]);
$view = $viewStmt->fetch();

if ($view) {
    // Jika sudah ada, tambah view count
    $updateViewStmt = $pdo->prepare("UPDATE page_views SET view_count = view_count + 1 WHERE page_name = ?");
    $updateViewStmt->execute([$pageName]);
} else {
    // Jika belum ada, buat record baru dengan view count 1
    $insertViewStmt = $pdo->prepare("INSERT INTO page_views (page_name, view_count) VALUES (?, 1)");
    $insertViewStmt->execute([$pageName]);
}

// Mendapatkan jumlah view untuk ditampilkan di halaman
$viewCountStmt = $pdo->prepare("SELECT view_count FROM page_views WHERE page_name = ?");
$viewCountStmt->execute([$pageName]);
$viewCount = $viewCountStmt->fetchColumn();

// Mengambil semua postingan
$postsStmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $postsStmt->fetchAll();

// Mengambil komentar untuk setiap postingan dengan nama pengguna
$commentsStmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY comments.created_at ASC");

// Mendapatkan jumlah kunjungan ke halaman user_dashboard
$pageName = 'user_dashboard';
$viewCountStmt = $pdo->prepare("SELECT view_count FROM page_views WHERE page_name = ?");
$viewCountStmt->execute([$pageName]);
$userDashboardViews = $viewCountStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }

        .dark {
            background-color: #1a202c; /* Warna latar belakang gelap */
            color: #e2e8f0; /* Warna teks terang */
        }

        .dark .bg-white {
            background-color: #2d3748; /* Warna latar belakang putih di mode gelap */
        }

        .dark .bg-gray-50 {
            background-color: #2d3748; /* Warna latar belakang abu-abu di mode gelap */
        }

        .dark .bg-blue-100 {
            background-color: #2b6cb0; /* Warna latar belakang biru di mode gelap */
        }

        .dark .bg-green-100 {
            background-color: #38a169; /* Warna latar belakang hijau di mode gelap */
        }

        .dark .bg-purple-100 {
            background-color: #6b46c1; /* Warna latar belakang ungu di mode gelap */
        }

        /* Menambahkan warna latar belakang isi postingan sama dengan warna judul postingan */
        .dark .post-content {
            background-color: #2d3748; /* Warna yang sama dengan latar belakang judul */
        }
    </style>

    <script>
        function confirmDeletePost() {
            return confirm("Apakah Anda yakin ingin menghapus postingan ini?");
        }

        function confirmDeleteComment() {
            return confirm("Apakah Anda yakin ingin menghapus komentar ini?");
        }

        function toggleComments(postId) {
            const commentList = document.getElementById(`comments-${postId}`);
            const isHidden = commentList.classList.contains('hidden');
            
            if (isHidden) {
                commentList.classList.remove('hidden');
            } else {
                commentList.classList.add('hidden');
            }
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark');
        }
    </script>
</head>
<body class="bg-[#f5f5e9]">
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

        <button onclick="toggleDarkMode()" class="absolute top-6 right-6 bg-gray-800 text-white p-2 rounded">
            <i class="fas fa-moon"></i>
        </button>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                <?php echo htmlspecialchars($_GET['message'] ?? ''); ?>
            </div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-4">Statistik Bulan Ini</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-blue-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Jumlah Postingan</h3>
                <p class="text-2xl font-semibold"><?php echo $postCount; ?></p>
            </div>
            <div class="p-4 bg-green-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Jumlah Komentar</h3>
                <p class="text-2xl font-semibold"><?php echo $commentCount; ?></p>
            </div>
            <div class="p-4 bg-purple-100 rounded-lg shadow">
                <h3 class="text-lg font-bold">Kunjungan User</h3>
                <p class="text-2xl font-semibold"><?php echo $userDashboardViews; ?></p>
            </div>
            <a href="post.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Tambah Postingan
                <i class="fa-regular fa-pen-to-square"></i>
            </a>
        </div>

        <h2 class="text-xl font-semibold mb-4">Postingan Terbaru</h2>
        <?php foreach ($posts as $post): ?>
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-600 rounded-lg shadow relative post-content">
                <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="text-gray-700 dark:text-gray-300 mt-2"><?php echo htmlspecialchars($post['content']); ?></p>

                <?php
                    $createdAt = new DateTime($post['created_at']);
                    $formattedDate = $createdAt->format('d M Y, H:i');
                ?>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1"><em><?php echo $formattedDate; ?></em></p>

                <div class="absolute top-0 right-0 mt-2 mr-2 flex space-x-2">
                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:underline">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                    <form action="delete_post.php" method="POST" class="inline" onsubmit="return confirmDeletePost();">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

                <h4 class="text-lg font-semibold mt-4">Komentar:</h4>
                <?php
                    $commentsStmt->execute([$post['id']]);
                    $commentCount = $commentsStmt->rowCount();
                ?>
                <button class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600" onclick="toggleComments(<?php echo $post['id']; ?>)">
                    <i class="fa-solid fa-comments"></i> <?php echo $commentCount; ?> Komentar
                </button>
                <div id="comments-<?php echo $post['id']; ?>" class="mt-2 hidden">
                    <?php while ($comment = $commentsStmt->fetch()): ?>
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mb-2">
                            <p class="font-bold"><?php echo htmlspecialchars($comment['username']); ?></p>
                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            <p class="text-gray-500 text-sm"><em><?php echo (new DateTime($comment['created_at']))->format('d M Y, H:i'); ?></em></p>
                            <form action="delete_comment.php" method="POST" onsubmit="return confirmDeleteComment();">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="flex justify-between mt-4">
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
        </div>
    </div>
</body>
</html>
