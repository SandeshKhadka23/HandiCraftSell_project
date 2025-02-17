<!-- register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
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
            background: #666;
            color: white;
            padding: 40px;
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .right-panel {
            padding: 40px;
            width: 60%;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input, select {
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

        .signin-link {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .signin-button {
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
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $role = $_POST["role"];

        $query = "SELECT * FROM User WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email already registered!');</script>";
        } else {
            $query = "INSERT INTO User (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
        }
    }
    ?>
    <div class="container">
        <div class="left-panel">
            <p>If you already have an account, just sign in.</p>
            <a href="login.php" class="signin-link">
                <div class="signin-button">SIGN IN</div>
            </a>
        </div>
        <div class="right-panel">
            <h2>Create your Account</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" required placeholder="NAME">
                </div>
                <div class="form-group">
                    <input type="email" name="email" required placeholder="EMAIL">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="PASSWORD">
                </div>
                <div class="form-group">
                    <select name="role" required>
                        <option value="buyer">Buyer</option>
                        <option value="artisan">Artisan</option>
                    </select>
                </div>
                <button type="submit">SIGN UP</button>
            </form>
        </div>
    </div>
</body>
</html>