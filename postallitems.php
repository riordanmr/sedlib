<!DOCTYPE html>
<html>
<head>
    <!-- Web page to accept HTML of items on hold.  We parse the HTML and
     insert rows into the database used by holds.php.
     Mark Riordan 2023-05-24
    -->
    <title>Load hold items</title>
    <style>
        body {font-family: Verdana; }
        h1 {font-size: 120%;}
        input {font-size: 100%;}
        pre {font-family: Menlo, Lucida Console, Consolas, Courier New;}
    </style>
</head>
<body>
    <h1>Load hold items</h1>
    <p>This web page can be used to load the current day's held items into
        the database for the <a href="holds.php">Holds</a> web application.
    </p>
    <ul>
        <li>In the Workflows program, go to Holds | On-Shelf Items and click on 
        the Current Location header to sort the items by location.</li>
        <li>Use File | Print Setup and choose to print to the application Notepad.exe.</li>
        <li>Click the Print button.</li>
        <li>Select all text in Notepad and do a copy.</li>
        <li>Paste into the textarea below.</li>
        <li>Enter the password.</li>
        <li>Click on Submit.</li>
    </ul>
    <form method="post" action="postallitems.php">
        <table>
            <tr>
                <td>Password:</td>
                <td><input type="text" name="password"></input>
            </tr>
            <tr>
                <td>Paste here:</td>
                <td><textarea id="items" name="items" cols="72" rows="15"></textarea></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Submit"></td>
            </tr>
        </table>
    </form>
    <pre><?php
    // This defines the DB_* constants used below.
    require_once '../../holds.config.php';
    function connectToDb() {
        // Create a new MySQLi object
        $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
        // Check the connection
        if ($connection->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $connection;
    }

    function clearExistingItems($connection) {
        $sql = "DELETE FROM items;";
        if($connection->query($sql) === false) {
            echo "Error deleting records: " . $connection->error;
            return false;
        }
        return true;
    }

    function getNextCharacterIndex($content, $searchString, $start) {
        $position = strpos($content, $searchString, $start);
    
        if ($position !== false) {
            $nextCharacterIndex = $position + strlen($searchString);
            return $nextCharacterIndex;
        }
    
        return -1; // If the search string is not found
    }
    
    function getTableCell($html, $start) {
        $result = false;
        $idxend = -1;
        $idx = strpos($html, "<td", $start);
        if(!($idx === false)) {
            $idx = strpos($html, ">", 1+$idx);
            if(!($idx === false)) {
                $idx++;
                // $idx is now just before the first char of the contents of the data cell.
                // Look for the end.
                $idxend = strpos($html, "</td", $idx);
                if(!($idxend === false)) {
                    $result = trim(substr($html, $idx, $idxend-$idx));
                }
            }
        }
        return array($result, $idxend);
    }
    
    function parseItem($itemText, $connection) {
        // Here's what a single item looks like in the input:
        // <tr>
        // <td valign="top" align="left">
        // 303.4833 NEWPORT</td>
        // <td valign="top" align="left">
        // 1</td>
        // <td valign="top" align="left">
        // Digital minimalism : choosing a focused life...</td>
        // <td valign="top" align="left">
        // 33701003001553</td>
        // <td valign="top" align="left">
        // BOOK</td>
        // <td valign="top" align="left">
        // NF</td>
        // </tr>
        $idx = 0;
        list($callNum, $idx) = getTableCell($itemText, $idx);
        list($copyNum, $idx) = getTableCell($itemText, $idx);
        list($itemTitle, $idx) = getTableCell($itemText, $idx);
        list($itemId, $idx) = getTableCell($itemText, $idx);
        list($itemType, $idx) = getTableCell($itemText, $idx);
        list($itemLoc, $idx) = getTableCell($itemText, $idx);
        //echo "callNum=$callNum copyNum=$copyNum title=$itemTitle itemId=$itemId itemType=$itemType itemLoc=$itemLoc\n";
        $stmt = $connection->prepare("INSERT INTO items (callnum, copynum, title, itemid, itemtype, curloc)" .
            " VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $callNum, $copyNum, $itemTitle, $itemId, $itemType, $itemLoc);
    
        if ($stmt->execute()  === TRUE) {
            //echo "New record inserted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $stmt->close();
    }
    
    function processAllItems($content, $connection)
    {
        // We can ignore the beginning part of the file, all the way through:
        // <b>Current location</b></td>
        // </tr>
        $idx = getNextCharacterIndex($content, "<b>Current location</b></td>", 0);
        if($idx >= 0) {
            $idxbeg = getNextCharacterIndex($content, "</tr>", $idx);
            //echo "main content starts: " . substr($content, $idxbeg, 70) . "\n";
        
            // Loop through the rest of the contents, parsing out the item information
            // in each row of the main table.
            $nItems = 0;
            do {
                $idxend = getNextCharacterIndex($content,"</tr>", $idxbeg);
                if($idxend < 0) break;
                $itemText = substr($content, $idxbeg, $idxend-$idxbeg);
                // Process only items that contain table data.  Curiously, the 
                // input file typically contains some empty table rows, which we ignore.
                if(str_contains($itemText,"<td")) {
                    $nItems++;
                    parseItem($itemText, $connection);
                }
                $idxbeg = $idxend;
            } while(true);
            echo "Loaded " . $nItems . " items.\n";
        } else {
            echo "That doesn't look like the HTML of items from Workflows.\n";
        }
    }
    
    function main() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $content = $_POST['items'];
            if($password == 'se') {
                echo "Received " . strlen($content) . " characters.\n";
                $connection = connectToDb();
                if($connection) {
                    if(clearExistingItems($connection)) {
                        processAllItems($content, $connection);
                    }
                    $connection->close();
                }
            } else {
                echo "Invalid password.";
            }
        }
    }
    main();
    ?>
    </pre>
</body>
</html>
