<?php
// cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location:../login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "HandicraftStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cart items
$user_id = $_SESSION["user_id"];
$sql_cart = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image, p.stock 
             FROM Cart c 
             JOIN Product p ON c.product_id = p.product_id 
             WHERE c.user_id = ?";
             
$stmt = $conn->prepare($sql_cart);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_cart = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .item-details {
            flex: 1;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            padding: 5px 10px;
            border: none;
            background: #4a90e2;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-btn {
            padding: 8px 16px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-summary {
            margin-top: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
        }

        #response-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }

        .success-message {
            background-color: #28a745;
            color: white;
        }

        .error-message {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div id="response-message"></div>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <img src="../artisan_folder/download.png" alt="Logo" class="logo">
                <span class="brand-name">NepArt Creations</span>
            </div>
            <div class="nav-right">
                <a href="buyer_dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="cart-badge" id="cart-count">
                        <?php echo $result_cart->num_rows; ?>
                    </span>
                </a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <h1>Shopping Cart</h1>
        
        <?php
        if ($result_cart->num_rows > 0) {
            $total = 0;
            while($row = $result_cart->fetch_assoc()) {
                $subtotal = $row["price"] * $row["quantity"];
                $total += $subtotal;
                ?>
                <div class="cart-item" data-cart-id="<?php echo $row['cart_id']; ?>">
                    <img src="../uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>Price: Rs. <?php echo number_format($row['price'], 2); ?></p>
                        <p>Subtotal: Rs. <span class="subtotal"><?php echo number_format($subtotal, 2); ?></span></p>
                    </div>
                    
                    <div class="item-actions">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $row['cart_id']; ?>, -1, <?php echo $row['stock']; ?>)">-</button>
                            <span class="quantity"><?php echo $row['quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $row['cart_id']; ?>, 1, <?php echo $row['stock']; ?>)">+</button>
                        </div>
                        <button class="remove-btn" onclick="removeCartItem(<?php echo $row['cart_id']; ?>)">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <p>Total Items: <span id="total-items"><?php echo $result_cart->num_rows; ?></span></p>
                <p>Total Amount: Rs. <span id="total-amount"><?php echo number_format($total, 2); ?></span></p>
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    Proceed to Checkout
                </button>
            </div>
            <?php
        } else {
            ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-3x"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to your cart and they will appear here</p>
                <a href="buyer_dashboard.php" class="checkout-btn">Continue Shopping</a>
            </div>
            <?php
        }
        ?>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@nepart.com</p>
                <p>Phone: +977-123456789</p>
                <p>Address: Kathmandu, Nepal</p>
            </div>
            <div class="quicklinks">
                <h3>Quick Links</h3>
                <a href="buyer_dashboard.php">Home</a><br>
                <a href="trackorders.php">Track Orders</a><br>
                <a href="cart.php">Cart</a>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <p>
                    <a href="#">Facebook</a> | 
                    <a href="#">Instagram</a> | 
                    <a href="#">Twitter</a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 ArtisanCraft. All rights reserved.</p>
        </div>
    </footer>

    <script>
    function showMessage(message, isSuccess = true) {
        const messageElement = document.getElementById('response-message');
        messageElement.textContent = message;
        messageElement.className = isSuccess ? 'success-message' : 'error-message';
        messageElement.style.display = 'block';
        
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 3000);
    }

    function updateCartQuantity(cartId, change, maxStock) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('change', change);

        fetch('update_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
                const quantityElement = cartItem.querySelector('.quantity');
                const subtotalElement = cartItem.querySelector('.subtotal');
                const currentQuantity = parseInt(quantityElement.textContent);
                const newQuantity = currentQuantity + change;
                
                if (newQuantity > 0 && newQuantity <= maxStock) {
                    location.reload();
                    showMessage('Cart updated successfully');
                } else {
                    showMessage('Invalid quantity', false);
                }
            } else {
                showMessage(data.message || 'Error updating cart', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error updating cart', false);
        });
    }

    function removeCartItem(cartId) {
        if (confirm('Are you sure you want to remove this item?')) {
            const formData = new FormData();
            formData.append('cart_id', cartId);

            fetch('remove_from_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                    showMessage('Item removed from cart');
                } else {
                    showMessage(data.message || 'Error removing item', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error removing item', false);
            });
        }
    }

    function proceedToCheckout() {
        window.location.href = 'checkout.php';
    }
    </script>
</body>
</html>