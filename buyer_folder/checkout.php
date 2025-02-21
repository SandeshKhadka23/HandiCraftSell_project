<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
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

// Fetch cart items and total
$user_id = $_SESSION["user_id"];
$sql_cart = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image 
             FROM Cart c 
             JOIN Product p ON c.product_id = p.product_id 
             WHERE c.user_id = ?";

$stmt = $conn->prepare($sql_cart);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_cart = $stmt->get_result();

// Calculate total
$total = 0;
while ($row = $result_cart->fetch_assoc()) {
    $total += $row["price"] * $row["quantity"];
}
$result_cart->data_seek(0); // Reset result pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleb.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input, 
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .payment-method {
            margin: 2rem 0;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .order-summary h2 {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .order-items {
            margin-bottom: 1.5rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
        }

        .place-order-btn {
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

        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
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

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
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
                </a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="checkout-form">
            <h1>Checkout</h1>
            <form id="checkoutForm" onsubmit="placeOrder(event)">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="street_address">Street Address *</label>
                    <input type="text" id="street_address" name="street_address" required>
                </div>

                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="district">District *</label>
                    <input type="text" id="district" name="district" required>
                </div>

                <div class="form-group">
                    <label for="special_instructions">Special Instructions (Optional)</label>
                    <textarea id="special_instructions" name="special_instructions"></textarea>
                </div>

                <div class="payment-method">
                    <label>
                        <input type="radio" name="payment_method" value="cod" checked>
                        Cash on Delivery
                    </label>
                    <p class="payment-info">Pay when you receive your items.</p>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php
                while ($row = $result_cart->fetch_assoc()) {
                    $subtotal = $row["price"] * $row["quantity"];
                    ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($row['product_name']); ?> (×<?php echo $row['quantity']; ?>)</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="total-line">
                <span>Total Amount:</span>
                <span>Rs. <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
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

    function placeOrder(event) {
        event.preventDefault();
        
        const formData = new FormData(document.getElementById('checkoutForm'));
        
        fetch('track_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Order placed successfully!');
                setTimeout(() => {
                    window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
                }, 2000);
            } else {
                showMessage(data.message || 'Error placing order', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error placing order', false);
        });
    }
    </script>
</body>
</html>