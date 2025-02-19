<?php
session_start();
include "../db.php";

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $query = "DELETE FROM Product WHERE product_id = ? AND artisan_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "Product deleted successfully!";
        header("Location: artisan_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "No product ID specified!";
}
?>
