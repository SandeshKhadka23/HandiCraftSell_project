<?php 
session_start(); 
include "../db.php"; 

// Check if user is logged in and is an artisan
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "artisan") {
    header("Location: ../login.php");
    exit();
}

// Fetch categories from database for dropdown
$categoryQuery = "SELECT * FROM Category";
$categoryResult = $conn->query($categoryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }

        form {
            display: grid;
            gap: 20px;
        }

        label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        input[type="file"] {
            padding: 10px 0;
        }

        button {
            background-color:#b91111;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #b91111;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            color: #333;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            input[type="text"],
            input[type="number"],
            textarea,
            select {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add a New Product</h2>

        <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>

            <div>
                <label for="price">Price:</label>
                <input type="number" id="price" step="0.01" name="price" required>
            </div>

            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div>
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" required>
            </div>

            <div>
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a Category</option>
                    <?php while ($row = $categoryResult->fetch_assoc()) { ?>
                        <option value="<?php echo $row['category_id']; ?>">
                            <?php echo htmlspecialchars($row['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label for="product_image">Product Image:</label>
                <input type="file" id="product_image" name="product_image" accept="image/*">
            </div>

            <button type="submit">Add Product</button>
        </form>

        <a href="artisan_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>