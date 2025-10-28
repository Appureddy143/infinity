<?php
    session_start();
    
    // If user is already logged in, redirect to home
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
    
    $errorMessage = '';

    // --- DATABASE CONNECTION (CONCEPT) ---
    // $dsn = "pgsql:host=...;port=...;dbname=...;user=...;password=...";
    // $pdo = new PDO($dsn);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($email) || empty($password)) {
            $errorMessage = 'Please fill out all fields.';
        } elseif ($password !== $confirmPassword) {
            $errorMessage = 'Passwords do not match.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email format.';
        } else {
            // Check if email already exists
            // $sql_check = "SELECT id FROM users WHERE email = ?";
            // $stmt_check = $pdo->prepare($sql_check);
            // $stmt_check->execute([$email]);
            // if ($stmt_check->fetch()) {
            //    $errorMessage = 'Email already in use.';
            // } else {
                
                // --- Create new user ---
                // $password_hash = password_hash($password, PASSWORD_BCRYPT);
                
                // $sql_insert = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
                // $stmt_insert = $pdo->prepare($sql_insert);
                
                // if ($stmt_insert->execute([$email, $password_hash])) {
                    // Success! Log the user in immediately
                    // $_SESSION['user_id'] = $pdo->lastInsertId();
                    // $_SESSION['user_email'] = $email;
                    // header("Location: index.php"); // Redirect to home
                    // exit;
                // } else {
                //     $errorMessage = 'Could not create account. Please try again.';
                // }
                
                // Placeholder success
                 $errorMessage = 'Account created (concept). Redirecting...';
                 header("Refresh: 2; url=index.php");

            // }
        }
    }
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MyStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans text-white">

    <div class="max-w-md mx-auto min-h-screen flex flex-col justify-center items-center p-6">
        <h1 class="text-3xl font-bold text-red-600 mb-6">Create Account</h1>

        <form action="register.php" method="POST" class="w-full">
            <?php if ($errorMessage): ?>
                <p class="text-red-400 mb-4"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input type="email" name="email" id="email" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500">
            </div>

            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500">
            </div>

            <button type="submit"
                    class="w-full bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition">
                Register
            </button>
        </form>
        
        <p class="mt-6 text-sm text-gray-400">
            Already have an account? 
            <a href="login.php" class="text-red-500 hover:underline">Login here</a>
        </p>
    </div>

</body>
</html>
