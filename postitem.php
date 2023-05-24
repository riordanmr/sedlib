<?php
// This defines the DB_* constants used below.
require_once '/var/www/holds.config.php';
function connectToDb() {
    // Create a new MySQLi object
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check the connection
    if ($connection->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $connection;
}

function processRequest($connection) {
    // Obtain the raw data from the request
    $json = file_get_contents('php://input');
    echo $json;
    // Convert it into a PHP object
    $data = json_decode($json);
    foreach($data as $key => $value) {
        print "$key => $value\n";
    }

    $stmt = $connection->prepare("UPDATE items SET status=?, notes=? WHERE itemid=?;");
    $stmt->bind_param("sss", $data->status, $data->notes, $data->itemId);

    if ($stmt->execute()  === TRUE) {
        //echo "New record inserted successfully";
        echo '{"operation": "ok"}';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $stmt->close();
}

function postItemMain() {
    $connection = connectToDb();
    processRequest($connection);
    $connection->close();
}

postItemMain();
?>