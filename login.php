<?php
// Start session at the very top
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in and redirect
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        header('Location: admin.php');
    } else {
        header('Location: profile.php');
    }
    exit;
}

// Get error message from URL
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center bg-gray-900 text-white p-4">

    <div class="w-full max-w-md">
        <div class="text-center">
            <a href="index.php" class="text-3xl font-bold text-red-500">YourStream</a>
            <h2 class="mt-4 text-2xl font-semibold text-white">Sign in to your account</h2>
        </div>

        <!-- THIS IS THE ERROR MESSAGE BANNER -->
        <?php if ($error): ?>
            <div class="bg-red-500 border border-red-700 text-white px-4 py-3 rounded-md relative my-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- THIS IS THE FORM WITH THE CORRECT ACTION -->
        <form class="mt-8 space-y-6 bg-gray-800 p-6 sm:p-8 rounded-lg shadow-xl" action="login_process.php" method="POST">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email address</label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Sign in
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">
            Don't have an account?
            <a href="register.php" class="font-medium text-red-400 hover:text-red-300">
                Sign up
            </a>
        </p>
    </div>

</body>
</html>


