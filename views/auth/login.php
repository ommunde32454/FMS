<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';

if(isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <i class="fas fa-leaf text-5xl text-emerald-600"></i>
            <h1 class="text-2xl font-bold mt-2 text-gray-800"><?php echo APP_NAME; ?></h1>
            <p class="text-gray-500 text-sm">Sign in to your account</p>
        </div>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                <?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>controllers/auth_login.php" method="POST">
            <?php echo CSRF::input(); ?>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" required class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 px-4 rounded hover:bg-emerald-700 transition">
                Login
            </button>
        </form>
    </div>
</body>
</html>