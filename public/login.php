<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Determine if the input is an email or username
    $column = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Query database based on email or username
    $stmt = $db->prepare("SELECT user_id, username, password FROM users WHERE $column = :username_or_email");
    $stmt->bindValue(':username_or_email', $username_or_email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username, email, or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            color: #333333;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            padding: 20px;
            text-align: center;
        }
        .container h1 {
            margin-bottom: 20px;
            color: #1e3c72;
        }
        .container label {
            display: block;
            text-align: left;
            font-size: 14px;
            margin: 10px 0 5px;
        }
        .container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .container button {
            width: 100%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .container button:hover {
            background: #163a59;
        }
        .container a {
            color: #2a5298;
            text-decoration: none;
            font-size: 14px;
        }
        .container a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form action="login.php" method="post">
            <label for="username_or_email">Email or Username:</label>
            <input type="text" id="username_or_email" name="username_or_email" placeholder="Email or Username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
