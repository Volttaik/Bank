<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO('sqlite:database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_id = uniqid('user_', true);

        // Generate a random 12-digit account number
        $account_number = str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        // Get referrer ID from hidden field
        $referrer_id = $_POST['referrer_id'];

        // Check if the email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $email_exists = $stmt->fetchColumn() > 0;

        if ($email_exists) {
            $error_message = "Email has already been used. Please use another one.";
        } else {
            // Insert new user into the database
            $stmt = $db->prepare(
                "INSERT INTO users (username, email, password, user_id, account_number, referrer_id)
                VALUES (:username, :email, :password, :user_id, :account_number, :referrer_id)"
            );
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindValue(':account_number', $account_number, PDO::PARAM_STR);
            $stmt->bindValue(':referrer_id', $referrer_id, PDO::PARAM_STR);
            $stmt->execute();

            // Insert referral record if referrer_id is not empty
            if (!empty($referrer_id)) {
                $stmt = $db->prepare(
                    "INSERT INTO referrals (referrer_id, referee_id)
                    VALUES (:referrer_id, :referee_id)"
                );
                $stmt->bindValue(':referrer_id', $referrer_id, PDO::PARAM_STR);
                $stmt->bindValue(':referee_id', $user_id, PDO::PARAM_STR);
                $stmt->execute();
            }

            $success_message = "Registration successful! <a href='login.php'>Login</a>";
        }
    } catch (PDOException $ex) {
        $error_message = "Error: " . $ex->getMessage();
    }
} else {
    // Retrieve referrer ID from URL
    $referrer_id = isset($_GET['referral_code']) ? $_GET['referral_code'] : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <h1>Register</h1>
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="referrer_id" value="<?php echo htmlspecialchars($referrer_id); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Register</button>
        </form>
        <p><a href="login.php">Already have an account? Login here</a></p>
    </div>
</body>
</html>
