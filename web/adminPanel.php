<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        h2 {
            color: #555;
            margin-top: 20px;
        }

        h3 {
            color: #777;
            margin-top: 10px;
        }

        ol {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            color: black;
        }

        th {
            background-color: grey;
        }

        h1 {
            margin-top: 50px;
            color: black;
            position: relative;
            font-weight: bold;
        }

        h1::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            background-color: #ccc;
            top: -10px; /* Расстояние от верхней границы элемента h1 до линии */
            left: 0;
        }
    </style>
</head>

<body>
    
    <?php

    if (isset($_SESSION['user_role'])) {
    
        if ($_SESSION['user_role'] === 'admin') {

             // Подключение к базе данных 
    $servername = "db";
    $username = "user";
    $password = "password";
    $dbname = "modgame"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Функция для выполнения запросов к базе данных и вывода результатов
    function executeQuery($conn, $sql)
    {
        $result = $conn->query($sql);

        if ($result === false) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        } else {
            return $result;
        }
    }

    // Отображение списка таблиц
    $tables = ['artist', 'asset', 'asset_type', 'developers', 'game', 'game_genre', 'game_mod', 'mod_type', 'programmer', 'users', 'admin'];


    echo "<ol>";
    foreach ($tables as $table) {
        echo "<li><a href='#$table'>$table</a></li>";
    }
    echo "</ol>";

    foreach ($tables as $table) {
        echo "<h1 id='$table'><a name='$table'>$table</a></h1>";
        
        $sqlDescribe = "DESCRIBE $table";
        $resultDescribe = executeQuery($conn, $sqlDescribe);

        echo "<h3>Описание:</h3><ul>";
        while ($row = $resultDescribe->fetch_assoc()) {
            echo "<li>{$row['Field']} ({$row['Type']})</li>";
        }
        echo "</ul>";

        echo "<p><a href='tableEditAdmin.php?table=$table'>Редактировать таблицу</a><p>";

        $sqlSelect = "SELECT * FROM $table";
        $resultSelect = executeQuery($conn, $sqlSelect);

        echo "<h3>Данные:</h3><table><tr>";
        $headers = array_keys($resultSelect->fetch_assoc());
        foreach ($headers as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr>";

        $resultSelect->data_seek(0);
        while ($row = $resultSelect->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
    }

    function getLatestMod($conn, $gameID) {
        $sql = "SELECT get_latest_mod($gameID) AS latestMod";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["latestMod"];
        } else {
            return "No Mods Available";
        }
    }

    if (isset($_POST['getLatestMod'])) {
        $gameID = $_POST['gameID'];
        $latestMod = getLatestMod($conn, $gameID);
        echo "Latest Mod: " . $latestMod;
    }

    if (isset($_POST["btnSearch"])) {
        $id_artist = $_POST["id_artist"];
        $rating = $_POST["rating"];
        $sql = "SELECT * FROM artist WHERE id_artist = $id_artist AND rating = $rating";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "ID Художника: " . $row["id_artist"] . ", Рейтинг: " . $row["rating"] .", Имя художника: " . $row["artist_name"] . ", Количество работ: " . $row["work_count"] . "<br>";
            }
        } else {
            echo "Ничего не найдено.";
        }
    }

    $conn->close();

        } else {
        
            echo "You do not have permission to access this page.";
        
        }
    } else {

        echo "You are not logged in.";
    }
    
    
    ?>
    


</body>

</html>
