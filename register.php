<?php
session_start();
include 'config.php';

$usernameError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userExists = $stmt->fetchColumn();

    if ($userExists) {
        // Username already exists
        $usernameError = 'Username sudah ada';
    } else {
        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $passwordHash, $role]);

        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Daftar Akun</h1>
            <form method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-semibold mb-2">Username</label>
                    <input type="text" name="username" placeholder="Masukkan Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" name="password" placeholder="Masukkan Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>
                <div class="mb-6">
                    <label for="role" class="block text-gray-700 font-semibold mb-2">Pilih Peran</label>
                    <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="user">User</option>
                        <!-- <option value="admin">Admin</option> -->
                    </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition duration-300">Daftar</button>
            </form>
            <p class="mt-4 text-center text-gray-600">Sudah punya akun? <a href="login.php" class="text-indigo-600 hover:underline">Login di sini</a></p>
        </div>
    </div>

    <script>
        // Check if there's an error and show SweetAlert
        <?php if ($usernameError): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $usernameError; ?>',
            });
        <?php endif; ?>
    </script>
</body>
</html>
