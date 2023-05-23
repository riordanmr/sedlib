<?php
// Program to populate a holds.items table from an input file.
// This test program reads a file containing the HTML resulting from
// having Workflows print "Holds | OnShelf Items" to an application. 
// This is to support "pulling holds" at the Sedona AZ Public Library.
// Mark Riordan  2023-05-21
function getNextCharacterIndex($content, $searchString, $start) {
    $position = strpos($content, $searchString, $start);

    if ($position !== false) {
        $nextCharacterIndex = $position + strlen($searchString);
        return $nextCharacterIndex;
    }

    return -1; // If the search string is not found
}

function readItemsFile() {
    $filename = "tmp/holds.html";
    if(!file_exists($filename)) {
        echo "!! file does not exist\n";
        $content = "";
    } else {
        $content = file_get_contents($filename);
    }
    return $content;
}

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
//        " VALUES ('$callNum', $copyNum, '$itemTitle', '$itemId', '$itemType', '$itemLoc')";

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
    echo "Parsed " . $nItems . " items.\n";
}

function main() {
    //echo "getTableCell ret '" . getTableCell(" <td class='x'>my table data.</td> <td>hi</td>") . "'\n";
    $content = readItemsFile();
    echo "file contains " . strlen($content) . " characters.\n";

    $connection = connectToDb();
    if($connection) {
        processAllItems($content, $connection);
        $connection->close();
    }
}



main();

?>
