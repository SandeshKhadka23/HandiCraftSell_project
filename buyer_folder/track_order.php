<?php
// process_order.php - This handles the order submission
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert into Orders table
    $user_id = $_SESSION["user_id"];
    $sql_order = "INSERT INTO Orders (user_id, status) VALUES (?, 'Pending')";
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Get cart items
    $sql_cart = "SELECT c.product_id, c.quantity, p.price 
                 FROM Cart c 
                 JOIN Product p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Insert order details for each cart item
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        
        $sql_details = "INSERT INTO OrderDetails (order_id, product_id, quantity, price_per_unit, subtotal) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_details);
        $stmt->bind_param("iiydd", $order_id, $row['product_id'], $row['quantity'], $row['price'], $subtotal);
        $stmt->execute();

        // Update product stock
        $sql_update_stock = "UPDATE Product 
                           SET stock = stock - ? 
                           WHERE product_id = ?";
        $stmt = $conn->prepare($sql_update_stock);
        $stmt->bind_param("ii", $row['quantity'], $row['product_id']);
        $stmt->execute();
    }

    // Clear user's cart
    $sql_clear_cart = "DELETE FROM Cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql_clear_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

<?php
// track_order.php - Modified to work with your schema
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location:../login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

if ($role == "buyer") {
    // For buyers: show their orders
    $sql = "SELECT o.order_id, o.order_date, o.status,
                   p.product_name, od.quantity, od.price_per_unit, od.subtotal,
                   SUM(od.subtotal) as total_amount
            FROM Orders o
            JOIN OrderDetails od ON o.order_id = od.order_id
            JOIN Product p ON od.product_id = p.product_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    // For artisans: show orders containing their products
    $sql = "SELECT o.order_id, o.order_date, o.status,
                   p.product_name, od.quantity, od.price_per_unit, od.subtotal,
                   u.full_name as buyer_name
            FROM Orders o
            JOIN OrderDetails od ON o.order_id = od.order_id
            JOIN Product p ON od.product_id = p.product_id
            JOIN User u ON o.user_id = u.user_id
            WHERE p.artisan_id = ?
            ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Rest of the HTML remains the same as before, but we'll add function to update order status -->

<script>
function updateOrderStatus(orderId) {
    const status = document.getElementById(`status-${orderId}`).value;
    
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Order status updated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || 'Error updating status', false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error updating status', false);
    });
}
</script>

<?php
// update_order_status.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify the artisan owns the products in this order
$sql_verify = "SELECT COUNT(*) as count 
               FROM OrderDetails od
               JOIN Product p ON od.product_id = p.product_id
               WHERE od.order_id = ? AND p.artisan_id = ?";
$stmt = $conn->prepare($sql_verify);
$stmt->bind_param("ii", $data['order_id'], $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    $sql_update = "UPDATE Orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $data['status'], $data['order_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}

$conn->close();
?>