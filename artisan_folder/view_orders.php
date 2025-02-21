<?php
session_start();
include "../db.php";

// Check if the user is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../login.php");
    exit();
}

$artisan_id = $_SESSION["user_id"];

// Fetch all orders containing products made by this artisan
$query = "
    SELECT DISTINCT o.order_id, o.order_date, o.status, 
           o.full_name, o.phone, o.email, o.street_address, o.city, o.district, 
           p.payment_status, p.amount
    FROM Orders o
    JOIN OrderDetails od ON o.order_id = od.order_id
    JOIN Product pr ON od.product_id = pr.product_id
    LEFT JOIN Payments p ON o.order_id = p.order_id
    WHERE pr.artisan_id = ?
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $artisan_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .order-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px;
            background: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 10px;
            color: white;
            background: #007bff;
            text-decoration: none;
            border-radius: 3px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <main>
        <h2>Orders</h2>

        <?php while ($order = $result->fetch_assoc()) { ?>
            <div class="order-card">
                <h3>Order #<?php echo htmlspecialchars($order["order_id"]); ?></h3>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order["full_name"]); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order["phone"]); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order["email"]); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order["street_address"] . ", " . $order["city"] . ", " . $order["district"]); ?></p>
                <p><strong>Order Date:</strong> <?php echo $order["order_date"]; ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order["status"]); ?></p>
                <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order["payment_status"] ?? "Not Paid"); ?></p>
                <p><strong>Amount Paid:</strong> $<?php echo number_format($order["amount"] ?? 0, 2); ?></p>

                <a href="handle_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn">View & Manage</a>
            </div>
        <?php } ?>
    </main>
</body>
</html>