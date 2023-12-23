<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;

function generateJwtToken($userId, $username) {
    $key = getenv('JWT_SECRET_KEY');
    if (empty($key)) {
        die("Error: JWT_SECRET_KEY not set in environment");
    }
    $tokenPayload = array(
        "iss" => "http://localhost:8080", // Replace with your issuer
        "aud" => "http://localhost:8080", // Replace with your audience
        "iat" => time(),
        "exp" => time() + 3600, // Token expiration time (1 hour)
        "userId" => $userId,
        "username" => $username,
        "userRole" => "admin", 
    );

    return JWT::encode($tokenPayload, $key, 'HS256'); // Added the algorithm 'HS256'
}


$servername = "db";
$username = "user";
$password = "password";
$dbname = "modgame";  // Specify your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, login, password FROM admin WHERE login = '$login' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $username = $row['login'];

        // Generate JWT token
        $token = generateJwtToken($userId, $username);
        setcookie("admin_jwt", $token, time() + 3600, "/"); // Replace with your desired cookie settings

        $_SESSION['user_role'] = 'admin';
        $response = array(
            'status' => 'success',
            'message' => 'Admin login successful',
            'token' => $token
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Неправильный логин или пароль'
        );
    }

    header('Content-Type: application/json; charset=utf-8'); // Set the correct content type and encoding
    echo json_encode($response);
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #222;
            color: #fff;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #444;
            border-radius: 5px;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #ffc107;
            color: #222;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-group button:hover {
            background-color: #ffca2c;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>AdminPanel</h2>
    <form action="" method="post" class="login-form">
        <div class="form-group">
            <label for="username">Логин:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <button type="submit">Вход</button>
        </div>
    </form>


</div>

</body>
</html>
