<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans text-white">

    <?php
        // --- DATABASE LOGIC (CONCEPT) ---
        session_start(); // Start the session at the very top

        // Check if user is already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        $errorMessage = '';

        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
            // 1. Connect to your Neon (PostgreSQL) database
            //    $pdo = new PDO($dsn, ...);
            
            // 2. Get form data
            $email = $_POST['email'];
            $password = $_POST['password'];

            // 3. Find the user
            //    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            //    $stmt->execute([$email]);
            //    $user = $stmt->fetch();

            // 4. Verify password
            //    if ($user && password_verify($password, $user['password_hash'])) {
            //        // Password is correct! Store user data in session
            //        $_SESSION['user_id'] = $user['id'];
            //        $_SESSION['user_email'] = $user['email'];
            //        
            //        // Redirect to home page
            //        header('Location: index.php');
            //        exit;
            //    } else {
            //        $errorMessage = 'Invalid email or password.';
            //    }

            // Placeholder logic:
            if ($email == 'user@example.com' && $password == 'password') {
                 $_SESSION['user_id'] = 123; // Placeholder user ID
                 $_SESSION['user_email'] = 'user@example.com';
                 header('Location: index.php');
                 exit;
            } else {
                 $errorMessage = 'Invalid email or password.';
            }
        }

        // Handle Registration (conceptual)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
             // 1. Get email, password, confirm_password
             // 2. Check if passwords match
             // 3. Check if email already exists
             // 4. Hash the password: $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
             // 5. Insert new user into `users` table
             // 6. Log them in (set session) and redirect
             $errorMessage = 'Registration is not implemented yet.';
        }
    ?>

    <div class="max-w-md mx-auto min-h-screen flex flex-col justify-center p-6">
        
        <!-- Header -->
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-red-600">MyStream</h1>
            <p class="text-gray-400">Sign in to continue</p>
        </header>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-6">
            <?php if (!empty($errorMessage)): ?>
                <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded-lg">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input type="email" name="email" id="email" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            <div>
                <button type="submit" name="login"
                        class="w-full bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition">
                    Sign In
                </button>
            </div>
        </form>

        <!-- Register Button (as a form submission) -->
        <form action="login.php" method="POST" class="mt-4">
            <p class="text-center text-gray-400">Don't have an account?</p>
            <!-- These would be hidden inputs for a real registration form -->
            <input type="hidden" name="email" value="new@example.com">
            <input type="hidden" name="password" value="newpass">
            
            <button type="submit" name="register"
                    class="w-full bg-gray-700 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-600 transition mt-2">
                Create Account
            </button>
        </form>

        <!-- Guest Mode -->
         <div class="text-center mt-6">
            <a href="index.php" class="text-gray-400 hover:text-white transition">Watch as Guest</a>
        </div>
    </div>

</body>
</html>
