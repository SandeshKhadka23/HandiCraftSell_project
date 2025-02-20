
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handicraft E-Commerce</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
    <header>
        <img src="artisan_folder/download.png" alt="Logo" class="logo">
        <span class="brand-name">NepArt Creations</span>
        <input type="text" placeholder="Search..." class="search-bar">
        <button class="login-btn">Login / Register</button>
    </header>
    
    <section class="about">
        <h1>About Us</h1>
        <p>Welcome to our handicraft store, where tradition meets creativity.</p>
        <div class="carousel">
            <div class="slider">
                <img src="image1.jpg">
                <img src="image2.jpg">
                <img src="image3.jpg">
                <img src="image4.jpg">
            </div>
        </div>
    </section>
    
    <section class="categories">
        <h1>Our Handicraft Categories</h1>
        <div class="category-container">
            <div class="category-item">
                <img src="handicraft1.jpg" alt="Wooden Crafts">
                <p>Wooden Crafts</p>
            </div>
            <div class="category-item">
                <img src="handicraft2.jpg" alt="Pottery">
                <p>Pottery</p>
            </div>
            <div class="category-item">
                <img src="handicraft3.jpg" alt="Handwoven Fabrics">
                <p>Handwoven Fabrics</p>
            </div>
            <div class="category-item">
                <img src="handicraft4.jpg" alt="Metal Crafts">
                <p>Metal Crafts</p>
            </div>
            <div class="category-item">
                <img src="handicraft5.jpg" alt="Stone Carvings">
                <p>Stone Carvings</p>
            </div>
        </div>
    </section>
    
    <script>
        let currentIndex = 0;
        const slider = document.querySelector(".slider");
        const totalImages = document.querySelectorAll(".slider img").length;

        function scrollImages() {
            if (currentIndex >= totalImages - 1) {
                currentIndex = 0;
                slider.style.transition = "none"; 
                slider.style.transform = "translateX(0)";
            } else {
                currentIndex++;
                slider.style.transition = "transform 3s ease-in-out";
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            }
        }

        setInterval(scrollImages, 3000);
    </script>
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@handicrafts.com</p>
                <p>Phone: +977-123456789</p>
                <p>Address: Kathmandu, Nepal</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <a href="#">Facebook</a> | <a href="#">Instagram</a> | <a href="#">Twitter</a>
            </div>
            <div class="footer-section">
                <p>&copy; 2025 Handicraft E-Commerce. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="script.js"></script> </script>
</body>
</html>

