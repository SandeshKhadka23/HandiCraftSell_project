<?php
session_start();
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

// Fetch categories
$sql_categories = "SELECT * FROM Category";
$result_categories = $conn->query($sql_categories);

// Fetch all products initially
$sql_products = "SELECT p.product_id, p.product_name, p.price, p.description, p.stock, p.image, p.category_id, c.category_name 
                 FROM Product p 
                 JOIN Category c ON p.category_id = c.category_id 
                 WHERE p.stock > 0";
$result_products = $conn->query($sql_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NepArt Creations</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="styleb.css">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-left">
            <img src="../artisan_folder/download.png" alt="Logo" class="logo">
            <span class="brand-name">NepArt Creations</span>
        </div>
        <div class="nav-right">
            <input type="text" placeholder="Search products..." class="search-bar">
            <select class="category-select" id="category-select" onchange="filterAndScrollToProducts()">
                <option value="">Search By Category</option>
                <?php
                if ($result_categories->num_rows > 0) {
                    while($row = $result_categories->fetch_assoc()) {
                        echo '<option value="' . $row["category_id"] . '">' . $row["category_name"] . '</option>';
                    }
                }
                ?>
            </select>
            <a href="cart.php" class="nav-link cart-link">
                <i class="fas fa-shopping-cart"></i> Cart
                <span class="cart-badge" id="cart-count">0</span>
            </a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</nav>


    <section class="about">
    <div class="slider-container">
        <div class="about-content">
            <h1>Welcome to NepArt Creations</h1>
            <p><i>"Handmade Heritage, Crafted with Love"</i></p>
            <br><br>
            <p>Find and Purchase Your Favorite Handicrafts at the Best Prices.</p>
        </div>
    </div>
</section>
</section>

    <section class="products" id="products-section">
        <h1>Featured Products</h1>
        <div class="product-container" id="product-container">
            <?php
            if ($result_products->num_rows > 0) {
                while($row = $result_products->fetch_assoc()) {
                    echo '<div class="product-item" data-category-id="' . $row["category_id"] . '">';
                    echo '<img src="../uploads/products/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="product-image">';
                    echo '<h2>' . htmlspecialchars($row["product_name"]) . '</h2>';
                    
                    echo '<p class="price">Price: Rs. ' . number_format($row["price"], 2) . '</p>';
                    
                    echo '<div class="quantity-selector">';
                    echo '<button class="qty-btn minus" onclick="updateQuantity(this, -1)">-</button>';
                    echo '<input type="number" class="qty-input" value="1" min="1" max="' . $row["stock"] . '" readonly>';
                    echo '<button class="qty-btn plus" onclick="updateQuantity(this, 1)">+</button>';
                    echo '</div>';
                    
                    echo '<div class="product-details" style="display: none;">';
                    echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '<p>Stock: ' . $row["stock"] . '</p>';
                    echo '<p>Category: ' . htmlspecialchars($row["category_name"]) . '</p>';
                    echo '</div>';
                    
                    echo '<div class="button-container">';
                    echo '<button class="details-btn" onclick="toggleDetails(this)">Show Details</button>';
                    echo '<button class="cart-btn" onclick="addToCart(this)" data-product-id="' . $row["product_id"] . '">Add to Cart</button>';
                    echo '</div>';
                    
                    echo '</div>';
                }
            } else {
                echo '<p class="no-products">No products available.</p>';
            }
            ?>
        </div>
    </section>

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
                <a href="cart.php">Cart</a><br>
                <a href="trackorders.php">Track Orders</a><br>
                <a href="#products-section">See Products</a>
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
    let cart = {};
    let cartCount = 0;

    function updateQuantity(button, change) {
        const qtyInput = button.parentElement.querySelector('.qty-input');
        let currentQty = parseInt(qtyInput.value);
        const maxQty = parseInt(qtyInput.max);

        if (change === 1 && currentQty < maxQty) {
            currentQty++;
        } else if (change === -1 && currentQty > 1) {
            currentQty--;
        }

        qtyInput.value = currentQty;
    }

    function toggleDetails(button) {
        const productItem = button.closest('.product-item');
        const details = productItem.querySelector('.product-details');
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
        button.textContent = details.style.display === 'block' ? 'Hide Details' : 'Show Details';
    }

    function addToCart(button) {
        const productItem = button.closest('.product-item');
        const productId = button.getAttribute('data-product-id');
        const qtyInput = productItem.querySelector('.qty-input');
        const quantity = parseInt(qtyInput.value);

        if (cart[productId]) {
            cart[productId].quantity += quantity;
        } else {
            const productName = productItem.querySelector('h2').textContent;
            const price = parseFloat(productItem.querySelector('.price').textContent.replace('Price: Rs. ', '').replace(',', ''));
            cart[productId] = { name: productName, price: price, quantity: quantity };
        }

        cartCount += quantity;
        updateCartCount();
        alert(`${quantity} x ${cart[productId].name} added to cart.`);
    }

    function updateCartCount() {
        const cartCountElement = document.getElementById('cart-count');
        cartCountElement.textContent = cartCount;
    }

    function filterAndScrollToProducts() {
        const selectedCategory = document.getElementById('category-select').value;
        const productItems = document.querySelectorAll('.product-item');

        productItems.forEach(item => {
            if (selectedCategory === "" || item.getAttribute('data-category-id') === selectedCategory) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        document.getElementById('products-section').scrollIntoView({ behavior: 'smooth' });
    }
</script>
<script>
    let currentIndex = 0;
    const totalImages = 3; // Total number of images in the slider

    function scrollImages() {
        currentIndex = (currentIndex + 1) % totalImages; // Increment index and loop back
        const sliderContainer = document.querySelector('.slider-container');

        // Update the background image based on the current index
        switch (currentIndex) {
            case 0:
                sliderContainer.style.backgroundImage = "linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('image1.jpg')";
                break;
            case 1:
                sliderContainer.style.backgroundImage = "linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('image2.jpg')";
                break;
            case 2:
                sliderContainer.style.backgroundImage = "linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('image3.jpg')";
                break;
        }
    }

    // Set interval for automatic sliding
    setInterval(scrollImages, 3000); // Change image every 3 seconds
</script>
</body>
</html>