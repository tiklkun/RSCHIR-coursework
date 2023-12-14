<?php
session_start();

if (isset($_SESSION['user_role'])) {
    // Check if the user's role is 'admin'
    $isAdmin = ($_SESSION['user_role'] === 'admin');
} else {
    // 'role' session variable is not set, assume the user is not an admin
    $isAdmin = false;
}
    if ($isAdmin){
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
        function executeQuery($conn, $sql)
        {
            $result = $conn->query($sql);

            if ($result === false) {
                echo "Error: " . $sql . "<br>" . $conn->error;
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
                echo "Ошибка: Таблица не имеет PRIMARY KEY";
            } else {
                // Подготовка запроса для обновления
                $updateSql = "UPDATE $table SET $updateField = '$updateValue' WHERE $primaryKeyName = '$primaryKey'";
                executeQuery($conn, $updateSql);
            }
        }

        // Обработка добавления записи
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
            executeQuery($conn, $sqlAdd);
        }



        if (isset($_POST['delete'])) {
            $deleteId = $_POST['delete_id'];

            if (!empty($deleteId) && is_numeric($deleteId)) {
                // Подготовленный запрос для удаления записи
                $sqlDelete = "DELETE FROM $table WHERE id_$table = $deleteId";
                executeQuery($conn, $sqlDelete);
            }
        }

        // Получение списка полей таблицы для сортировки
        $sqlDescribe = "DESCRIBE $table";
        $resultDescribe = executeQuery($conn, $sqlDescribe);

        $sortableFields = [];
        while ($row = $resultDescribe->fetch_assoc()) {
            $sortableFields[] = $row['Field'];
        }

        // Получение данных таблицы для отображения
        $sqlSelect = "SELECT * FROM $table";
        $resultSelect = executeQuery($conn, $sqlSelect);

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
<?php if ($isAdmin): ?>
<p><a href='adminPanel.php'>назад</a><p>
<h1>
    <?php echo $table; ?>
</h1>
<!-- Обновление записи -->
<h2>Обновление записи</h2>
<form method="post" action="tableEditAdmin.php?table=<?php echo $table; ?>">
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
<form method="post" action="tableEditAdmin.php?table=<?php echo $table; ?>">
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
        echo "<td><form method='post' action='tableEditAdmin.php?table=$table'>
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
<?php $conn->close();?>
<?php else: ?>
    <p>You do not have access to this page</p>
<?php endif; ?>

</body>
</html>
