<?php
require 'config.php'; // Include configuration file
session_start();

if (!isset($_SESSION['username'])) {  // Changed from user_id to username
    header('Location: login.php');
    exit;
}

$transaction_id = htmlspecialchars($_GET['transaction_id'] ?? '');
$sender = htmlspecialchars($_GET['sender'] ?? '');
$recipient = htmlspecialchars($_GET['recipient'] ?? '');
$amount = htmlspecialchars($_GET['amount'] ?? '');
$bank = htmlspecialchars($_GET['bank'] ?? 'Skyways Bank'); // Default to Skyways Bank
$time = date('Y-m-d H:i:s'); // Current server time

// Transfer breakdown
$cash = 0;
$check = 0;
$online = floatval($amount);
$total = $cash + $check + $online;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 80px;
        }
        .header h1 {
            font-size: 24px;
            margin: 10px 0;
        }
        .divider {
            border-bottom: 2px solid #0d47a1;
            margin: 20px 0;
        }
        .content {
            font-size: 14px;
        }
        .content .row {
            margin-bottom: 10px;
        }
        .content .row span {
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .table td:last-child {
            text-align: right;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-section div {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .signature-section .line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <img src="c882c5dc4f9096e0599d9de9593a732c.jpg" alt="Bank Logo">
            <h1>Transfer Receipt</h1>
            <p>Authorized by Devatron</p>
        </div>

        <!-- Info Section -->
        <div class="content">
            <div class="row">
                <span>Transaction ID:</span> <?php echo $transaction_id; ?>
            </div>
            <div class="row">
                <span>Sender:</span> <?php echo $_SESSION['username']; ?> <!-- Display user name -->
            </div>
            <div class="row">
                <span>Recipient:</span> <?php echo $recipient; ?>
            </div>
            <div class="row">
                <span>Amount:</span> $<?php echo number_format($amount, 2); ?>
            </div>
            <div class="row">
                <span>Bank:</span> <?php echo $bank; ?>
            </div>
            <div class="row">
                <span>Time:</span> <?php echo $time; ?>
            </div>
        </div>

        <!-- Transfer Breakdown Table -->
        <div class="divider"></div>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cash</td>
                    <td>Cash Transfer</td>
                    <td>$<?php echo number_format($cash, 2); ?></td>
                </tr>
                <tr>
                    <td>Cheque</td>
                    <td>Cheque Transfer</td>
                    <td>$<?php echo number_format($check, 2); ?></td>
                </tr>
                <tr>
                    <td>Online</td>
                    <td>Online Transfer</td>
                    <td>$<?php echo number_format($online, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Total Amount -->
        <div class="total">
            <span>Total Amount: $<?php echo number_format($total, 2); ?></span>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div>Authorized Representative: Sky Bank</div>
            <div>Signature:</div>
            <div class="line"></div>
        </div>
    </div>
</body>
</html>
