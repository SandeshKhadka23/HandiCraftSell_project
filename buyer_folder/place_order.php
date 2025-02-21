<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get form data
$user_id = $_SESSION["user_id"];
$full_name = $_POST["full_name"];
$phone = $_POST["phone"];
$email = $_POST["email"];
$street_address = $_POST["street_address"];
$city = $_POST["city"];
$district = $_POST["district"];
$special_instructions = $_POST["special_instructions"];
$payment_method = $_POST["payment_method"];

// Insert into Orders table
$sql_order = "INSERT INTO Orders (user_id, full_name, phone, email, street_address, city, district, special_instructions, payment_method) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param(
    "issssssss",
    $user_id,
    $full_name,
    $phone,
    $email,
    $street_address,
    $city,
    $district,
    $special_instructions,
    $payment_method
);
$stmt_order->execute();
$order_id = $stmt_order->insert_id;

if (!$order_id) {
    echo json_encode(["success" => false, "message" => "Failed to create order"]);
    exit();
}

// Fetch cart items
$sql_cart = "SELECT c.product_id, c.quantity, p.price 
             FROM Cart c 
             JOIN Product p ON c.product_id = p.product_id 
             WHERE c.user_id = ?";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

// Insert into OrderDetails table
while ($row = $result_cart->fetch_assoc()) {
    $product_id = $row["product_id"];
    $quantity = $row["quantity"];
    $price_per_unit = $row["price"];
    $subtotal = $price_per_unit * $quantity;

    $sql_details = "INSERT INTO OrderDetails (order_id, product_id, quantity, price_per_unit, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt_details = $conn->prepare($sql_details);
    $stmt_details->bind_param("iiidd", $order_id, $product_id, $quantity, $price_per_unit, $subtotal);
    $stmt_details->execute();
}

// Clear the cart
$sql_clear_cart = "DELETE FROM Cart WHERE user_id = ?";
$stmt_clear_cart = $conn->prepare($sql_clear_cart);
$stmt_clear_cart->bind_param("i", $user_id);
$stmt_clear_cart->execute();

echo json_encode(["success" => true, "order_id" => $order_id]);
?>