<?php
session_start();
include "../db.php";

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../login.php");
    exit();
}

// Fetch order details for updating
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $query = "SELECT * FROM Orders WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        echo "Order not found!";
        exit();
    }
} else {
    echo "No order ID specified!";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST["status"];
    $update_query = "UPDATE Orders SET status = ? WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $status, $order_id);
    $update_stmt->execute();

    if ($update_stmt->affected_rows > 0) {
        echo "<div class='success-message'>Order status updated successfully!</div>";
    } else {
        echo "<div class='error-message'>Failed to update order status.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #333;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            select {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Order Status</h2>

        <form action="" method="POST">
            <div class="form-group">
                <label for="status">Order Status:</label>
                <select id="status" name="status" required>
                    <option value="Pending" <?php if ($order["status"] == "Pending") echo "selected"; ?>>Pending</option>
                    <option value="Shipped" <?php if ($order["status"] == "Shipped") echo "selected"; ?>>Shipped</option>
                    <option value="Delivered" <?php if ($order["status"] == "Delivered") echo "selected"; ?>>Delivered</option>
                    <option value="Cancelled" <?php if ($order["status"] == "Cancelled") echo "selected"; ?>>Cancelled</option>
                </select>
            </div>

            <button type="submit">Update Status</button>
        </form>

        <a href="view_order.php" class="back-link">Back to Orders</a>
    </div>
</body>
</html>