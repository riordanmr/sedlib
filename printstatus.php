<html>
<head>
<!-- printstatus.php displays the library items that had been put on hold,
  and their status after holds processing has been done.  Items are listed
  in 4 categories: not found, problem with item, still looking (normally there
  should be no items in this category by the time we view this page), and
  items which were found (most items should have this status.).
  See holds.php for the web page which is used to manage the statuses.
  Developed by Mark Riordan for Sedona AZ library 2023-05-26
-->
<meta http-equiv="Content-Type"content="text/html; charset=iso-8859-1">
<title>Status of hold items</title>
<style>
h2 {
    margin-top: 0.7em;
    margin-bottom: 0.14em;
}
table {
    border-collapse: collapse;
}

tr {
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
}
table.print-friendly tr td, table.print-friendly tr th {
    page-break-inside: avoid;
}
.tablenoborders {
    border-top: 0px; border-bottom: 0px;
}
.idsmall {
    font-size: 60%;
}
.rownobotline {
    border-bottom: 0px;
}
.rownotopline {
    border-top: 0px;
}
.notes {
    font-family: Georgia; font-style: italic; font-size: 125%;
}
</style>
<!-- Author: Mark Riordan -->
</head>

<body>
<font face="Verdana">
<table width="100%" border="0" cellpadding="2" bordercolor="#FFFFFF">
<tr class="tablenoborders">
<td valign="top" align="left" colwidth="100.0%" colspan="6">
Status of Hold Items for SED <?php echo date('m/d/Y');?></td>
</tr>

<!-- <tr>
<td valign="top" align="left" colwidth="26.666666666666668%">
 </td>
</tr> -->

<tr>
<td valign="top" align="left">
<b>Call Number</b></td>
<td valign="top" align="left">
<b>Copy</b></td>
<td valign="top" align="left">
<b>Title</b></td>
<td valign="top" align="left">
<b>Item ID</b></td>
<td valign="top" align="left">
<b>Item type</b></td>
<td valign="top" align="left">
<b>Current location</b></td>
</tr>
<?php
    // This defines the DB_* constants used below.
    require_once '../../holds.config.php';
    function connectToDb() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        // Create a new MySQLi object
        $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
        // Check the connection
        try {  
            if ($connection->connect_error) {
                echo "Connection failed: " . $connection->connect_error;
            }
        } catch(exception $e) {
            echo "Exception: Connection failed: " . $connection->connect_error;
        }
        return $connection;
    }

    function listRecsWhere($connection, $where, $statusDesc) {
        $sql = "SELECT * FROM items WHERE $where ORDER BY id;";
        $result = $connection->query($sql);
        $prevStatus = "";
        if ($result->num_rows > 0) {
            echo "\n<tr><td colspan='6'><h2>$statusDesc</h2></td>\n";
            // Loop through each row of the result set
            while ($row = $result->fetch_assoc()) {
                // Access the column values using the column names
                $callNum = htmlspecialchars($row["callnum"]);
                $copyNum = htmlspecialchars($row["copynum"]);
                $title = htmlspecialchars($row["title"]);
                $itemId = htmlspecialchars($row["itemid"]);
                $itemType = htmlspecialchars($row["itemtype"]);
                $curLoc = htmlspecialchars($row["curloc"]);
                $status = htmlspecialchars($row["status"]);
                $notes = htmlspecialchars($row["notes"]);

                $classtop = "";
                if(strlen($notes)>0) {
                    $classtop = " class='rownobotline'";
                }
                $itemIdSpecial = "<span class='idsmall'>" . substr($itemId, 1, strlen($itemId)-4) . "&nbsp;</span>" . substr($itemId, strlen($itemId)-4);
                echo "<tr$classtop><td>$callNum</td><td>$copyNum</td><td>$title</td><td>$itemIdSpecial</td><td>$itemType</td><td>$curLoc</td></tr>\n";
                if(strlen($notes)>0) {
                    echo "<tr class='rownotopline notes'><td></td><td colspan='5'>$notes</td></td>\n";
                }
                $prevStatus = $status;
            }
        }
        $result->close();
    }

    function doMain() {
        $connection = connectToDb();
        listRecsWhere($connection, "status='cantfind'",'Items not found');
        listRecsWhere($connection, "status='problem'", 'Items with problems');
        listRecsWhere($connection, "status=''", 'Items for which we are still looking');
        listRecsWhere($connection, "status='found'", 'Items that were found');
        $connection->close();
    }
    doMain();
?>
</table>
</font>
</body>
</html>
