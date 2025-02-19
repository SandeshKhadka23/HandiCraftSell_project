<?php
session_start();
include "../db.php";

// Check if the user is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["order_id"])) {
    die("Order ID is required.");
}

$order_id = $_GET["order_id"];

// Fetch order details
$query = "
    SELECT o.order_id, o.order_date, o.status, u.username, u.email
    FROM Orders o
    JOIN User u ON o.user_id = u.user_id
    WHERE o.order_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch ordered items
$query = "
    SELECT p.product_name, od.quantity, od.price_per_unit, (od.quantity * od.price_per_unit) AS subtotal
    FROM OrderDetails od
    JOIN Product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>




<!-- html code section -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order["order_id"]; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <nav>
        <h2>Artisan Dashboard</h2>
        <ul>
            <li><a href="view_orders.php">Manage Orders</a></li>
        </ul>
    </nav>

    <main>
        <div class="order-details">
            <h2>Order #<?php echo $order["order_id"]; ?></h2>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order["username"]); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order["email"]); ?></p>
            <p><strong>Order Date:</strong> <?php echo $order["order_date"]; ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order["status"]); ?></p>

            <h3>Ordered Items</h3>
            <?php while ($row = $items->fetch_assoc()) { ?>
                <div class="item-card">
                    <p><strong>Product:</strong> <?php echo htmlspecialchars($row["product_name"]); ?></p>
                    <p><strong>Quantity:</strong> <?php echo $row["quantity"]; ?></p>
                    <p><strong>Price Per Unit:</strong> $<?php echo number_format($row["price_per_unit"], 2); ?></p>
                    <p><strong>Subtotal:</strong> $<?php echo number_format($row["subtotal"], 2); ?></p>
                </div>
            <?php } ?>

            <form action="update_order.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <label for="status">Update Status:</label>
                <select name="status">
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Completed">Completed</option>
                </select>
                <button type="submit">Update Order</button>
            </form>
        </div>
    </main>

</body>
</html>
