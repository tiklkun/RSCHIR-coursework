<?php
session_start();
    // Подключение к базе данных 
$servername = "db";
$username = "user";
$password = "password";
$dbname = "modgame"; 
 
$conn = new mysqli($servername, $username, $password, $dbname);
global $conn; 
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if it's an API request
if (isset($_GET['api'])) {
    handleApiRequest();
} else {
    renderHtml($conn);
}

function handleApiRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Check if it's a specific API endpoint
        if (isset($requestData['action'])) {
            $action = $requestData['action'];

            switch ($action) {
                case 'getLatestMod':
                    handleGetLatestMod($requestData);
                    break;
                case 'getGameDetails':
                    handleGameDetailsRequest($requestData);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Action not specified']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
    }
}



function handleGetLatestMod($requestData) {
    global $conn; 
    if (isset($requestData['gameID'])) {
        $gameID = $requestData['gameID'];
        $latestMod = getLatestMod($conn, $gameID);
        echo json_encode(['latestMod' => $latestMod]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing gameID']);
    }
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

function handleGameDetailsRequest($requestData) {
    global $conn;

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($requestData["game_ID"])) {
        $gameID = $requestData["game_ID"];

        // Check if gameID is not equal to 0 before executing the query
        if ($gameID != 0) {
            $sql = "CALL get_game_details($gameID)";
            $result = $conn->multi_query($sql);

            if ($result) {
                do {
                    if ($result = $conn->store_result()) {
                        // Construct an associative array with game details
                        $gameDetails = [];
                        while ($row = $result->fetch_assoc()) {
                            foreach ($row as $key => $value) {
                                $gameDetails[$key] = $value;
                            }
                        }

                        // Encode the array as JSON and echo it
                        
                        echo json_encode($gameDetails);

                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error calling procedure: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid gameID']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
}





function executeQuery($conn, $sql){
    $result = $conn->query($sql);

    if ($result === false) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    } else {
        return $result;
    }
}

function displayTables($conn) {
    $tables = ['game', 'game_genre', 'game_mod', 'mod_type'];

    echo "<ol>";
    foreach ($tables as $table) {
        echo "<li><a href='#$table'>$table</a></li>";
    }
    echo "</ol>";
 
    foreach ($tables as $table) {
        echo "<h1 id='$table'><a name='$table'>$table</a></h1>";
       
        if ($table == "game_mod" && $_SESSION['user_role'] == 'user') {
            echo "<p><a href='tableEditUser.php?table=$table'>Редактировать таблицу</a><p>";
        }
 
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
}

function renderHtml($conn) {
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="styles.css">';
    echo '<title>Modgame</title>';
    echo '<style>';
    echo 'body { font-family: \'Arial\', sans-serif; background-color: #f4f4f4; margin: 20px; }';
    echo 'h1 { color: #333; }';
    echo 'h2 { color: #555; margin-top: 20px; }';
    echo 'h3 { color: #777; margin-top: 10px; }';
    echo 'ol { list-style: none; padding: 0; }';
    echo 'li { margin-bottom: 5px; }';
    echo 'table { width: 100%; border-collapse: collapse; margin-top: 10px; }';
    echo 'th, td { padding: 10px; border: 1px solid #ddd; text-align: left; color: black; }';
    echo 'th { background-color: grey; }';
    echo 'h1 { margin-top: 50px; color: black; position: relative; font-weight: bold; }';
    echo 'h1::before { content: \'\'; position: absolute; width: 100%; height: 3px; background-color: #ccc; top: -10px; left: 0; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';

    
    if (!isset($_SESSION['user_role'])) {
        echo"<a href='register.php'>";
        echo "<button class='form-group button-signup'>Зарегистрироваться</button>";
        echo "</a>";
 
        echo "<a href='login.php'>";
        echo "<button class='form-group button-signin'>Войти</button>";
        echo "</a>";
     }
    // Your HTML content goes here...

    $jwtSecretKey = bin2hex(random_bytes(32));
    echo $jwtSecretKey;

    // Displaying tables...
    displayTables($conn);

    $conn->close();

    echo '</body>';
    echo '</html>';


}


?>
