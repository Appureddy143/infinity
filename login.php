<?php
    session_start();
    // If user is already logged in, redirect to home
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $error = '';
    // Check for error messages from login_process.php
    if (isset($_GET['error'])) {
        if ($_GET['error'] == '1') {
            $error = 'Invalid email or password.';
        } elseif ($_GET['error'] == '2') {
            $error = 'An error occurred. Please try again.';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f;
            color: #ffffff;
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black flex flex-col items-center justify-center p-6">
        
        <h1 class="text-3xl font-bold text-red-500 mb-8">Login to YourStream</h1>
        
        <form action="login_process.php" method="POST" class="w-full max-w-sm">
            
            <?php if ($error): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</dlabel>
                <input type="email" id="email" name="email" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="you@example.com" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</dlabel>
                <input type="password" id="password" name="password" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg text-lg transition-colors duration-200">
                Log In
            </button>
        </form>

        <p class="mt-8 text-sm text-gray-400">
            Don't have an account? 
            <a href="register.php" class="font-medium text-red-500 hover:text-red-400">Sign Up</a>
        </p>
    </div>

</body>
</html>


