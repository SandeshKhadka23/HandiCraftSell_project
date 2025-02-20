<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION["user_id"];

    // Get the product price
    $stmt = $conn->prepare("SELECT price FROM Product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $price_per_unit = $row['price'];

        // Check if the product already exists in the cart
        $stmt = $conn->prepare("SELECT cart_id, quantity FROM Cart WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Product already in cart, update quantity
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;

            // Update the quantity in the cart
            $update_stmt = $conn->prepare("UPDATE Cart SET quantity = ?, price_per_unit = ? WHERE cart_id = ?");
            $update_stmt->bind_param("idi", $new_quantity, $price_per_unit, $row['cart_id']);
            $update_stmt->execute();
        } else {
            // Product not in cart, insert new record
            $insert_stmt = $conn->prepare("INSERT INTO Cart (user_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("iiii", $user_id, $product_id, $quantity, $price_per_unit);
            $insert_stmt->execute();
        }

        echo json_encode(['success' => true, 'product_name' => $_POST['product_name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>