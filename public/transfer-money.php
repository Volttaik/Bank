<?php
require 'db.php'; // Assuming this file sets up the $db connection
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$recipient_username = ''; // Variable to store recipient's username
$bank_type = 'Skyways Bank'; // Bank type constant

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_account_number = $_POST['recipient_account_number'];
    $amount = (float)$_POST['amount'];

    if ($amount <= 0 || empty($recipient_account_number)) {
        $error = 'Invalid amount or recipient account number.';
    } else {
        try {
            $db->beginTransaction();

            // Check if recipient exists by account number and fetch their username
            $stmt = $db->prepare("SELECT user_id, username, balance FROM users WHERE account_number = :account_number");
            $stmt->bindValue(':account_number', $recipient_account_number, PDO::PARAM_STR);
            $stmt->execute();
            $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($recipient) {
                $recipient_username = $recipient['username']; // Store recipient's username

                // Check sender's balance
                $stmt = $db->prepare("SELECT balance FROM users WHERE user_id = :user_id");
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->execute();
                $sender = $stmt->fetch(PDO::FETCH_ASSOC);
                $sender_balance = $sender['balance'];

                if ($amount <= $sender_balance) {
                    // Deduct from sender's balance
                    $stmt = $db->prepare("UPDATE users SET balance = balance - :amount WHERE user_id = :user_id");
                    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                    $stmt->execute();

                    // Add to recipient's balance
                    $stmt = $db->prepare("UPDATE users SET balance = balance + :amount WHERE user_id = :user_id");
                    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                    $stmt->bindValue(':user_id', $recipient['user_id'], PDO::PARAM_STR);
                    $stmt->execute();

                    // Record transaction
                    $transaction_id = uniqid('txn_', true); // Generate a unique transaction ID
                    $stmt = $db->prepare("INSERT INTO transactions (transaction_id, sender_id, receiver_id, transaction_type, amount)
                                          VALUES (:transaction_id, :sender_id, :receiver_id, 'transfer', :amount)");
                    $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
                    $stmt->bindValue(':sender_id', $user_id, PDO::PARAM_STR);
                    $stmt->bindValue(':receiver_id', $recipient['user_id'], PDO::PARAM_STR);
                    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
                    $stmt->execute();

                    // Insert notifications for sender
                    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, transaction_id)
                                          VALUES (:user_id, :message, :transaction_id)");
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                    $stmt->bindValue(':message', 'You have transferred ' . number_format($amount, 2) . ' to ' . $recipient_username . ' (' . $recipient_account_number . ') at ' . $bank_type, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
                    $stmt->execute();

                    // Insert notifications for recipient
                    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, transaction_id)
                                          VALUES (:user_id, :message, :transaction_id)");
                    $stmt->bindValue(':user_id', $recipient['user_id'], PDO::PARAM_STR);
                    $stmt->bindValue(':message', 'You have received ' . number_format($amount, 2) . ' from ' . $user_id . ' (' . $recipient_account_number . ') at ' . $bank_type, PDO::PARAM_STR);
                    $stmt->bindValue(':transaction_id', $transaction_id, PDO::PARAM_STR);
                    $stmt->execute();

                    $db->commit();

                    // Redirect to the success page with transaction details
                    header("Location: transaction_success.php?transaction_id=$transaction_id&sender=$user_id&recipient=$recipient_username&amount=$amount&bank=$bank_type");
                    exit;
                } else {
                    $error = 'Insufficient balance.';
                }
            } else {
                $error = 'Recipient account does not exist.';
            }
        } catch (PDOException $ex) {
            $db->rollBack();
            $error = 'Error: ' . $ex->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 80%; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); }
        h1 { color: #007bff; }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin: 5px 0 20px; border: 1px solid #ddd; border-radius: 5px; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .status { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Transfer Money</h1>
        <?php if ($error) { ?>
            <p class="status"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form method="POST" action="">
            <label for="recipient_account_number">Recipient Account Number:</label>
            <input type="text" id="recipient_account_number" name="recipient_account_number" required>
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" required>
            <button type="submit">Transfer Money</button>
        </form>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
