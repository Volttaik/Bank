<?php
require 'db.php';
session_start(); // Start the session

// Define the admin ID
$admin_id = 'user_674233f50d0b23.70918193'; // Replace with your actual admin ID

$error = '';
$success = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the account number from the POST request
    $account_number = $_POST['account_number'] ?? '';
    $amount = (float)$_POST['amount'];

    // Validate the amount
    if ($amount <= 0) {
        $error = 'Invalid amount. Please enter a positive number.';
    } else {
        try {
            // Fetch user ID and current balance using account number
            $stmt = $db->prepare("SELECT user_id, balance FROM users WHERE account_number = :account_number");
            $stmt->bindValue(':account_number', $account_number, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists
            if (!$user) {
                $error = 'Account not found.';
            } else {
                $current_balance = $user['balance'];
                $user_id = $user['user_id']; // Get user ID for transactions

                // Check if the logged-in user is the admin
                if ($_SESSION['user_id'] !== $admin_id) {
                    $error = 'Only the admin can add money.';
                } else {
                    // Update balance
                    $new_balance = $current_balance + $amount;
                    $stmt = $db->prepare("UPDATE users SET balance = :balance WHERE account_number = :account_number");
                    $stmt->bindValue(':balance', $new_balance, PDO::PARAM_STR);
                    $stmt->bindValue(':account_number', $account_number, PDO::PARAM_STR);
                    $stmt->execute();

                    // Record the transaction
                    $transaction_id = uniqid('txn_', true); // Generate a unique transaction ID
                    $stmt = $db->prepare("INSERT INTO transactions (transaction_id, sender_id, receiver_id, transaction_type, amount)
                                          VALUES (:transaction_id, :sender_id, :receiver_id, 'deposit', :amount)");
                    $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
                    $stmt->bindValue(':sender_id', $admin_id, PDO::PARAM_STR); // Use admin ID as sender
                    $stmt->bindValue(':receiver_id', $user_id, PDO::PARAM_STR); // Use user ID for receiver
                    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                    $stmt->execute();

                    // Insert a notification for the user
                    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, transaction_id)
                                          VALUES (:user_id, :message, :transaction_id)");
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                    $stmt->bindValue(':message', 'Your balance has been updated by ' . number_format($amount, 2) . '.', PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
                    $stmt->execute();

                    $success = 'Money successfully added to the account. New balance: ' . number_format($new_balance, 2);
                }
            }
        } catch (PDOException $ex) {
            $error = 'Error: ' . htmlspecialchars($ex->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Money</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 80%; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); }
        h1 { color: #007bff; }
        label { display: block; margin: 10px 0 5px; }
        input[type="number"], input[type="text"] { width: 100%; padding: 10px; margin: 5px 0 20px; border: 1px solid #ddd; border-radius: 5px; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .status { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deposit Money</h1>
        <?php if ($error) { ?>
            <p class="status"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <?php if ($success) { ?>
            <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
        <?php } ?>
        <form method="POST" action="">
            <label for="account_number">Account Number:</label>
            <input type="text" id="account_number" name="account_number" required>

            <label for="amount">Amount to Deposit:</label>
            <input type="number" id="amount" name="amount" min="0" step="any" required>

            <button type="submit">Deposit Money</button>
        </form>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
