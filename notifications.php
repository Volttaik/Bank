<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch all notifications
$stmt = $db->prepare("SELECT n.*, t.transaction_id, t.date, t.time
                       FROM notifications n
                       LEFT JOIN transactions t ON n.transaction_id = t.transaction_id
                       WHERE n.user_id = :user_id
                       ORDER BY n.timestamp DESC");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skyways Banking - Notifications</title>
    <style>
        /* General Reset */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f8ff; /* Light blue background */
            color: #333; /* Dark text for contrast */
        }

        /* Header */
        h1 {
            text-align: center;
            color: #0047ab; /* Dark blue */
            margin: 20px 0;
        }

        /* Notifications List */
        ul {
            list-style: none;
            padding: 0;
            margin: 0 auto;
            max-width: 800px;
        }

        li {
            background: #e6f2ff; /* Soft blue */
            border: 1px solid #0047ab; /* Dark blue border */
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        li p {
            margin: 5px 0;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            text-align: center;
            text-decoration: none;
            background-color: #0047ab; /* Dark blue */
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #003580; /* Darker blue */
        }
    </style>
</head>
<body>
    <h1>Notifications</h1>
    <ul>
        <?php foreach ($notifications as $notification) { ?>
            <li>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                <?php if ($notification['transaction_id']) { ?>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($notification['transaction_id']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($notification['date']); ?></p>
                    <p><strong>Time:</strong> <?php echo htmlspecialchars($notification['time']); ?></p>
                <?php } ?>
                <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($notification['timestamp']); ?></p>
            </li>
        <?php } ?>
    </ul>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
