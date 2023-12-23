<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = apache_request_headers();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if ($token === null) {
    die("Token is null");
}


function validateJwtToken($token) {
    try {
        $key = getenv('JWT_SECRET_KEY');
        if (empty($key)) {
            throw new Exception("Error: JWT_SECRET_KEY not set in environment");
        }

        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // Check if the decoded token has the 'user_role' claim and its value is 'user' or 'admin'
        if (isset($decoded->userRole) && ($decoded->userRole === "user" || $decoded->userRole === "admin")) {
            return true;
        } else {
            return false;
        }
    } catch (Firebase\JWT\ExpiredException $e) {
        // Token has expired
        return false;
    } catch (Firebase\JWT\BeforeValidException $e) {
        // Token is not yet valid
        return false;
    } catch (Exception $e) {
        // Other exceptions
        die("Error: " . $e->getMessage());
    }
}

if (!validateJwtToken($token)) {
    // Unauthorized access
    die("Unauthorized access");
}


// Check if the modified table belongs to game_mod, asset, or developers
$allowedTables = ['game_mod', 'asset', 'developers'];
$table = $_GET['table'];

if (!$table || !in_array($table, $allowedTables)) {
    echo "Error: Invalid or missing table";
    exit;
}



// Подключение к базе данных (замените данными вашей базы данных)
$servername = "db";
$username = "user";
$password = "password";
$dbname = "modgame"; // Укажите имя вашей базы данных

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Функция для выполнения запросов к базе данных и вывода результатов
// Function for executing queries and handling errors
function executeQuery($conn, $sql)
{
    $result = $conn->query($sql);

    if ($result === false) {
        return ["error" => "Error: " . $sql . "<br>" . $conn->error];
    } else {
        return $result;
    }
}


// Получение имени таблицы из параметра запроса
$table = $_GET['table'];

if (!$table) {
    echo "Ошибка: Не указана таблица.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_all'])) {
    $sqlSelectAll = "SELECT * FROM $table";
    $resultSelectAll = executeQuery($conn, $sqlSelectAll);

    if ($resultSelectAll === false) {
        $response = ["error" => "Error fetching data from the table"];
    } else {
        $entries = [];
        while ($row = $resultSelectAll->fetch_assoc()) {
            $entries[] = $row;
        }
        $response = ["entries" => $entries];
    }

    // Respond with JSON
    echo json_encode($response);
    exit;
}

// Обновление записи
// Обновление записи
if (isset($_POST['update'])) {
    $updateField = $conn->real_escape_string($_POST['update_field']);
    $updateValue = $conn->real_escape_string($_POST['update_value']);
    $primaryKey = $conn->real_escape_string($_POST['primary_key']);

    $sqlDescribe = "DESCRIBE $table";
    $resultDescribe = executeQuery($conn, $sqlDescribe);

    $primaryKeyName = '';
    while ($row = $resultDescribe->fetch_assoc()) {
        if (strpos($row['Key'], 'PRI') !== false) {
            $primaryKeyName = $row['Field'];
            break;
        }
    }

    if (empty($primaryKeyName)) {
        $response = ["error" => "Table does not have a PRIMARY KEY"];
    } else {
        // Prepare update query
        $updateSql = "UPDATE $table SET $updateField = '$updateValue' WHERE $primaryKeyName = '$primaryKey'";
        $result = executeQuery($conn, $updateSql);

        if (isset($result["error"])) {
            $response = ["error" => $result["error"]];
        } else {
            // Fetch the updated fields
// Fetch the updated fields
            $sqlSelectUpdated = "SELECT * FROM $table WHERE $primaryKeyName = '$primaryKey'";
            $resultSelectUpdated = executeQuery($conn, $sqlSelectUpdated);

            if (is_array($resultSelectUpdated) && isset($resultSelectUpdated["error"])) {
                $response = ["error" => $resultSelectUpdated["error"]];
            } else {
                $updatedFields = $resultSelectUpdated->fetch_assoc();
                $response = ["success" => true, "updatedFields" => $updatedFields];
            }

        }
    }

    // Respond with JSON
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Получение списка полей таблицы
    $sqlDescribe = "DESCRIBE $table";
    $resultDescribe = executeQuery($conn, $sqlDescribe);

    $fields = [];
    while ($row = $resultDescribe->fetch_assoc()) {
        if ($row['Extra'] !== 'auto_increment') {
            $fields[] = $row['Field'];
        }
    }

    // Подготовленный запрос для добавления записи
    $sqlAdd = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES (";
    foreach ($fields as $field) {
        $sqlAdd .= "'{$_POST[$field]}', ";
    }
    $sqlAdd = rtrim($sqlAdd, ', ') . ")";
    
    // Execute the query
    $resultAdd = executeQuery($conn, $sqlAdd);

    if (isset($resultAdd["error"])) {
        // Handle the case where the query execution fails
        $response = ["error" => $resultAdd["error"]];
    } else {
        // Fetch the newly added record
        $lastInsertId = $conn->insert_id;
        $sqlSelectNewEntry = "SELECT * FROM $table WHERE id_$table = $lastInsertId";
        $resultSelectNewEntry = executeQuery($conn, $sqlSelectNewEntry);

        if ($resultSelectNewEntry instanceof mysqli_result) {
            // Return the new entry in the JSON response
            $newEntry = $resultSelectNewEntry->fetch_assoc();
            $response = ["success" => true, "newEntry" => $newEntry];
        } else {
            // Handle the case where fetching the new entry fails
            $response = ["error" => "Error fetching data from the table"];
        }
    }

    // Respond with JSON
    echo json_encode($response);
    exit;
}


if (isset($_POST['delete'])) {
    $deleteId = $_POST['delete_id'];

    if (!empty($deleteId) && is_numeric($deleteId)) {
        // Подготовленный запрос для удаления записи
        $sqlDelete = "DELETE FROM $table WHERE id_$table = $deleteId";
        $resultDelete = executeQuery($conn, $sqlDelete);

        if (isset($resultDelete["error"])) {
            // Handle the case where the query execution fails
            $response = ["error" => $resultDelete["error"]];
        } else {
            // Return success response
            $response = ["success" => true, "message" => "Record deleted successfully"];
        }
    } else {
        // Handle the case where deleteId is empty or not numeric
        $response = ["error" => "Invalid deleteId"];
    }

    // Respond with JSON
    echo json_encode($response);
    exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $table; ?></title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>

<p><a href='index.php'>назад</a><p>
<h1><?php echo $table; ?></h1>
<!-- Обновление записи -->
<h2>Обновление записи</h2>
<form method="post" action="tableEditUser.php?table=<?php echo $table; ?>">
    <label for="update_field">Название поля для обновления:</label>
    <input type="text" name="update_field" required>
    <label for="update_value">Новое значение:</label>
    <input type="text" name="update_value" required>
    <label for="primary_key">Primary Key:</label>
    <input type="text" name="primary_key" required>
    <input type="submit" name="update" value="Обновить">
</form>

<!-- Добавление записи -->
<h2>Добавление записи</h2>
<form method="post" action="tableEditUser.php?table=<?php echo $table; ?>">
    <?php
    foreach ($sortableFields as $field) {
        if ($field !== "id_$table") {
            echo "<label for='$field'>$field:</label>";
            echo "<input type='text' name='$field' required><br>";
        }
    }
    ?>
    <input type="submit" name="add" value="Добавить">
</form>

<!-- Сортировка таблицы -->
<h2>Список записей</h2>
<label for="sort_field">Сортировать по полю:</label>
<select id="sort_field" onchange="sortTable()">
    <?php
    foreach ($sortableFields as $field) {
        echo "<option value='$field'>$field</option>";
    }
    ?>
</select>

<label for="sort_order">Порядок сортировки:</label>
<select id="sort_order" onchange="sortTable()">
    <option value="asc">По возрастанию</option>
    <option value="desc">По убыванию</option>
</select>

<table id="data_table">
    <tr>
        <?php
        // Вывод заголовков таблицы
        $resultSelectHeader = executeQuery($conn, $sqlDescribe);
        while ($row = $resultSelectHeader->fetch_assoc()) {
            echo "<th>{$row['Field']}</th>";
        }
        echo "<th>Действия</th>";
        ?>
    </tr>
    <?php
    // Вывод данных таблицы
    while ($row = $resultSelect->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>$value</td>";
        }
        echo "<td><form method='post' action='tableEditUser.php?table=$table'>
                  <input type='hidden' name='delete_id' value='{$row["id_$table"]}'>
                  <input type='submit' name='delete' value='Удалить'></form></td>";
        echo "</tr>";
    }
    ?>
</table>

<script>
    function sortTable() {
        var table = document.getElementById("data_table");
        var selectedField = document.getElementById("sort_field").value;
        var sortOrder = document.getElementById("sort_order").value;
        var headerRow = table.rows[0];
        var index = -1;
        for (var i = 0; i < headerRow.cells.length; i++) {
            if (headerRow.cells[i].innerText.trim() === selectedField) {
                index = i;
                break;
            }
        }
        if (index !== -1) {
            var switching = true;
            while (switching) {
                switching = false;
                var rows = table.rows;
                for (var i = 1; i < (rows.length - 1); i++) {
                    var shouldSwitch = false;
                    var x = rows[i].getElementsByTagName("td")[index].innerHTML;
                    var y = rows[i + 1].getElementsByTagName("td")[index].innerHTML;
                    var isNumeric = !isNaN(parseFloat(x)) && isFinite(x) && !isNaN(parseFloat(y)) && isFinite(y);
                    if (isNumeric) {
                        x = parseFloat(x);
                        y = parseFloat(y);
                    }
                    if ((sortOrder === "asc" && x > y) || (sortOrder === "desc" && x < y)) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }
    }
</script>

</body>
</html>

