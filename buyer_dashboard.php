<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: login.php"); // Redirect unauthorized users
    exit();
}
?>
<div class="welcomemsg">
<h1>Welcome,<?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
</div>
<p>Browse and purchase amazing handicrafts from artisans.</p>

<ul>
    <li><a href="view_products.php">Browse Products</a></li>
    <li><a href="track_orders.php">Track Your Orders</a></li>
    <li><a href="wishlist.php">View Wishlist</a></li>
</ul>

<a href="logout.php">Logout</a>
