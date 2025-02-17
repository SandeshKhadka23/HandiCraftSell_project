<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
<p>Your role: <?php echo htmlspecialchars($_SESSION["role"]); ?></p>
<a href="logout.php">Logout</a>
