<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Database connection
require 'db.php';

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch user balance and account number
$stmt = $db->prepare("SELECT balance, account_number FROM users WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

$balance = $user['balance'];
$account_number = $user['account_number'];

// Fetch the two most recent transactions with sender and receiver details
$stmt = $db->prepare("
    SELECT t.transaction_type, t.amount, t.timestamp,
           CASE WHEN t.transaction_type = 'Transfer' THEN u_sender.username ELSE '' END AS sender_username,
           CASE WHEN t.transaction_type = 'Transfer' THEN u_receiver.username ELSE '' END AS receiver_username
    FROM transactions t
    LEFT JOIN users u_sender ON t.sender_id = u_sender.user_id
    LEFT JOIN users u_receiver ON t.receiver_id = u_receiver.user_id
    WHERE t.sender_id = :user_id OR t.receiver_id = :user_id
    ORDER BY t.timestamp DESC
    LIMIT 2
");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skyways Banking - Dashboard</title>
    <!-- Link to the external CSS stylesheet -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="header">
        <!-- Profile Section with Picture and Username -->
        <div class="profile-info">
            <img src="1a49e2e9b87ed2611add5e09f4a2c7d1.jpg" alt="Profile Icon" class="profile-img">
            <h1>Hi, <?php echo htmlspecialchars($username); ?></h1>
        </div>

        <div>
            <img src="bb0b1650acbaff4a756501d33116c1b9.jpg" alt="Customer Service">
            <img src="f63d428685d0845401a5445ed8505cbf.jpg" alt="Scan URL Code">
            <a href="notifications.php">
                <img src="b72a628ecb15e9a17456fb1bbd5275f0.jpg" alt="Notifications">
            </a>
        </div>
    </div>

    <div class="profile-section">
        <img src="c882c5dc4f9096e0599d9de9593a732c.jpg" alt="Profile Icon" class="profile-img">
    </div>

    <div class="balance-container">
        <div class="balance" id="accountBalance">
            Account Balance: $<?php echo number_format($balance, 2); ?><br>
            <span class="account-number">Account Number:</span> <span class="account-number"><?php echo htmlspecialchars($account_number); ?></span>
        </div>
        <div class="actions">
            <button onclick="window.location.href='deposit-money.php'">Add Money</button>
        </div>
    </div>

    <div class="info-box">
        <p>Stay informed about the newest things on Skyways.</p>
        <img src="5fbaa885d94300ccbb9914ab00265cce.jpg" alt="Go">
    </div>

    <div class="icons-container">
        <div class="icons-grid">
            <a href="buy-data.php"><img src="e44da9901771d7106cfa694f2eeee39d.jpg" alt="Buy Data"></a>
            <a href="pay-bills.php"><img src="5e18400ac878c519cfe6c99faffe9c57.jpg" alt="Pay Bills"></a>
            <a href="transfer-money.php"><img src="0767e2f027af2c82b56d234fb33c4167.jpg" alt="Transfer Money"></a>
            <a href="deposit-money.php"><img src="0767e2f027af2c82b56d234fb33c4167_1.jpg" alt="Deposit Money"></a>
            <a href="giveaway.php"><img src="360_F_214798289_xgvrhWyPUwi8e6p7wnDJ98LfcYyKvJXi.jpg" alt="Giveaway"></a>
            <a href="more-options.php"><img src="b1687f7fe70a1b53c85865601260e45b.jpg" alt="More Options"></a>
            <a href="betting-options.php"><img src="7dff841399ab65a4f6e54e5189d2bdaa.jpg" alt="Betting Options"></a>
            <a href="tv-options.php"><img src="05205f25b6fbb70461e990da022af3d1_1.jpg" alt="TV Option"></a>
        </div>
    </div>

    <div class="recent-transactions">
        <h2>Recent Transactions</h2>
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-item">
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($transaction['transaction_type']); ?></p>
                    <p><strong>Amount:</strong> $<?php echo number_format($transaction['amount'], 2); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($transaction['timestamp']); ?></p>
                    <?php if ($transaction['transaction_type'] == 'Transfer'): ?>
                        <p><strong>Sender:</strong> <?php echo htmlspecialchars($transaction['sender_username']); ?></p>
                        <p><strong>Receiver:</strong> <?php echo htmlspecialchars($transaction['receiver_username']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recent transactions.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="icons-row">
            <a href="profile.php"><img src="b6a9b9149f74f6fb7a6ce839ab43834d.jpg" alt="Profile"></a>
            <a href="settings.php"><img src="e349bc0e8d1c00e3e3e13d88dc8e0e1d.jpg" alt="Settings"></a>
            <a href="tax.php"><img src="e799a2b53cb68953d28eefe4824f72a7.jpg" alt="Tax"></a>
            <a href="reward.php"><img src="04ea54ce79681ae6e23fad6188306ebb.jpg" alt="Reward"></a>
        </div>
</body>
</html>
