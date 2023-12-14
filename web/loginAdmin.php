<?php
session_start();
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

    <?php

    $servername = "db";
    $username = "user";
    $password = "password";
    $dbname = "modgame";  // Укажите имя вашей базы данных

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
            $_SESSION['user_role'] = 'admin';
            // Пользователь найден, переход на другую страницу
            header("Location: adminPanel.php");
            exit();
        } else {
            echo "Неправильный логин или пароль";
        }
    }
    $conn->close();
    ?>
</div>

</body>
</html>
