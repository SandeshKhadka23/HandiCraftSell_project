document.addEventListener("DOMContentLoaded", () => {
    const hamburgerMenu = document.querySelector(".hamburger-menu");
    const navLinks = document.querySelector(".nav-links");

    // Toggle mobile menu
    hamburgerMenu.addEventListener("click", () => {
        navLinks.classList.toggle("active");
    });

    // Highlight active menu on scroll
    const sections = document.querySelectorAll("section");
    const navItems = document.querySelectorAll(".nav-links a");

    window.addEventListener("scroll", () => {
        let current = "";

        sections.forEach((section) => {
            const sectionTop = section.offsetTop - 80;
            if (scrollY >= sectionTop) {
                current = section.getAttribute("id");
            }
        });

        navItems.forEach((item) => {
            item.classList.remove("active");
            if (item.getAttribute("href") === `#${current}`) {
                item.classList.add("active");
            }
        });
    });
});


document.addEventListener("DOMContentLoaded", function() {
    let currentIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
    }

    prevButton.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(currentIndex);
    });

    nextButton.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    });

    setInterval(function() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }, 5000);
});

// Smooth scrolling to categories
function scrollToCategories() {
    const categorySection = document.getElementById("categories");
    categorySection.scrollIntoView({ behavior: "smooth" });
}


document.addEventListener("DOMContentLoaded", () => {
    const cartIcon = document.querySelector(".cart-icon");
    const cartDropdown = document.querySelector(".cart-dropdown");

    // Toggle cart dropdown visibility
    cartIcon.addEventListener("click", () => {
        cartDropdown.style.display =
            cartDropdown.style.display === "block" ? "none" : "block";
    });

    // Hide cart dropdown when clicking outside
    document.addEventListener("click", (e) => {
        if (!cartIcon.contains(e.target) && !cartDropdown.contains(e.target)) {
            cartDropdown.style.display = "none";
        }
    });
});

// Redirect to shop on button click
function returnToShop() {
    alert("Redirecting to the shop...");
    window.location.href = "/shop"; // Update this URL as needed
}