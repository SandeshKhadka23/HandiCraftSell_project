<!-- login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            min-height: 100vh;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            max-width: 900px;
            margin: 20px auto;
        }

        .left-panel {
            padding: 40px;
            width: 60%;
        }

        .right-panel {
            background: #666;
            color: white;
            padding: 40px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-bottom: 1px solid #ddd;
            outline: none;
            font-size: 14px;
            color: #666;
        }

        input::placeholder {
            color: #999;
            text-transform: uppercase;
            font-size: 12px;
        }

        .forgot-password {
            text-align: right;
            font-size: 12px;
            color: #999;
            text-decoration: none;
            margin-top: 5px;
            display: block;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #C4A484;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
        }

        button:hover {
            background: #B8926A;
        }

        .signup-link {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .signup-button {
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    include "db.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $query = "SELECT * FROM User WHERE email = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            if ($_SESSION["role"] == "artisan") {
                header("Location:artisan_folder\artisan_dashboard.php");
            } else {
                header("Location: buyer_dashboard.php");
            }            
            exit();
        } else {
            echo "<script>alert('Invalid email or password!');</script>";
        }
    }
    ?>
    <div class="container">
        <div class="left-panel">
            <h2>Welcome</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" required placeholder="EMAIL">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="PASSWORD">
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit">SIGN IN</button>
            </form>
        </div>
        <div class="right-panel">
            <p>Don't have an account? Please Sign up!</p>
            <a href="register.php" class="signup-link">
                <div class="signup-button">SIGN UP</div>
            </a>
        </div>
    </div>
</body>
</html>
