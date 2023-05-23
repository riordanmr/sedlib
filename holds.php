<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type"content="text/html; charset=iso-8859-1">
<!-- This web page displays a list of items on hold, and allows the user
 to mark items as found or not found.
 This is to support "pulling holds" at the Sedona AZ Public Library.
 Mark Riordan  2023-05-21
-->
<title>Onshelf Items</title>
    <!-- Prevent Safari from rendering random text as phone numbers. -->
    <meta name="format-detection" content="telephone=no">
    <meta name="x-detect-telephone" content="no">
<style>
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
body {
    font-size: 350%; font-family: Verdana;
}
.thinline {
    border-top: 0.5px solid;
    margin-top: 2pt; margin-bottom: 2pt;
}
.itemdiv {
    
}
.found {
    background-color: #d0ffd0;
    color: gray;
}

.stilllooking {
}

.cantfind {
    background-color: #ffc0c0;
}

.itemCallNum {
    color: "darkgray";
}

.idsmall {
    font-size: 50%;
}
    .itemtitle {
        font-family: Georgia;
    }
    
    /* From https://www.w3schools.com/howto/howto_css_modals.asp */
    /* The Modal (background) */
    .modal {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: green; /*rgb(0,0,0);*/ /* Fallback color */
      background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .modalcontent {
      background-color: #fefefe;
      margin: 5% auto; /* from the top and centered */
      padding: 1em;
      border: 1px solid #888;
      width: 95%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
      color: #aaa;
      float: right;
      font-size: 1.5em;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .myButton {
        font-size: 150%;
        width: 80%;
    }

    .notes {
        font-size: 100%;
    }
    
</style>
<!-- Author: Mark Riordan -->
    <script>
        var modal; 
        var currentId;

        function Init() {
            //alert("Init here");
            for (elem of document.getElementsByTagName('div')){
              if(elem.getAttribute("class") == "itemdiv") {
                  //var id = elem.id;
                  //alert("Setting onclick for " + elem.id);
                  // This does nothing:
                  //elem.onclick = "onItemClick();";
                  // This always calls onItemClick with the id of the last div:
                  // elem.onclick = function() {onItemClick(id);};
                  elem.addEventListener('click', function(e) { 
                      onItemClick(e);
                  });
              }
            }

            modal = document.getElementById("myModal");
            
            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
              if (event.target == modal) {
                modal.style.display = "none";
              }
            }
        }

        // Function to open the modal
        function openModal() {
            modal.style.display = "block";
            document.body.style.overflow = "hidden"; // Prevent scrolling of the page
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
            document.body.style.overflow = "auto"; // Restore scrolling of the page
        }

        function getAllProperties(obj) {
            var result="";
            for(propName in obj) {
                result += propName + "='" + obj[propName] + "'; ";
            }
            return result;
        }

        function onItemClick(e) {
            var id = e.currentTarget.id;
            currentId = id;
            //alert("onItemClick for e " + getAllProperties(e));
            document.getElementById("myModal").style.display = "block";
            document.getElementById("myModalContent").style.display = "block";
            //alert("For id " + id + " we have " + getAllProperties(document.getElementById(id).firstChild.nextElementSibling));
            document.getElementById("promptItem").innerHTML = document.getElementById(id).firstChild.nextElementSibling.innerText;
            //modal.style.display = "block";
            //modalcontent.style.display = "block";
        }

        function markFound() {
            document.getElementById(currentId).className = "itemdiv found";
        }

        function markStillLooking() {
            document.getElementById(currentId).className = "itemdiv stilllooking";
        }

        function markCantFind() {
            //alert("currentId=" +currentId + " props: " + getAllProperties(document.getElementById(currentId)));
            document.getElementById(currentId).className = "itemdiv cantfind";
        }
    </script>
</head>

<body onload="Init();">
    <!-- Modal dialog to prompt user what to do
    -->
    <!-- The Modal -->
    <div id="myModal" class="modal">

      <!-- Modal content -->
      <div id="myModalContent" class="modalcontent">
        <span onclick="closeModal();" class="close">&times;</span>
        <p id="promptItem">Some text in the Modal..</p>
        <p><button class="myButton" onclick="markFound(); closeModal();">Found</button></p>
        <p><button class="myButton" onclick="markStillLooking(); closeModal();">Still looking</button></p>
        <p><button class="myButton" onclick="markCantFind(); closeModal();">Can't find</button></p>
        <p><textarea id="notes" name="notes" class="notes" rows="3"></textarea></p>
      </div>

    </div>
    <?php
    // This defines the DB_* constants used below.
    require_once '/var/www/holds.config.php';
    function connectToDb() {
        $servername = "localhost";
        $username = "lhg";
        $password = "cdc6500";
        $dbname = "holds";
    
        // Create a new MySQLi object
        $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
        // Check the connection
        if ($connection->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $connection;
    }

    function listRecsWhere($connection, $where) {
        $sql = "SELECT * FROM items WHERE $where ORDER BY id;";
        $result = $connection->query($sql);
        $prevLoc = "";
        if ($result->num_rows > 0) {
            // Loop through each row of the result set
            while ($row = $result->fetch_assoc()) {
                // Access the column values using the column names
                $callNum = htmlspecialchars($row["callnum"]);
                $title = htmlspecialchars($row["title"]);
                $itemId = htmlspecialchars($row["itemid"]);
                $curLoc = htmlspecialchars($row["curloc"]);
                $status = htmlspecialchars($row["status"]);
                $notes = htmlspecialchars($row["notes"]);

                if($prevLoc != $curLoc) {
                    echo "\n<h2>$curLoc</h2>\n";
                }

                echo "<div id='$itemId' class='itemdiv'>\n";
                echo "<span class='itemCallNum'>$callNum</span><br/>\n";
                echo "<span class='itemtitle'>$title</span><br/>\n";
                $itemIdSpecial = "<span class='idsmall'>" . substr($itemId, 1, strlen($itemId)-4) . "</span> " . substr($itemId, strlen($itemId)-4);
                echo "$itemIdSpecial\n";
                echo "</div>";
                echo "<hr class='thinline'/>\n";
                $prevLoc = $curLoc;
            }
        }
    }

    function doMain() {
        $connection = connectToDb();
        listRecsWhere($connection, "curloc not like 'J%'");
        listRecsWhere($connection, "curloc like 'J%'");
        $connection->close();
    }
    doMain();
    ?>
<!--     
    <h2>BIO</h2>
<div id="i0890" class="itemdiv">
<span class="itemCallNum">BIOGRAPHY POLLEY, S.</span><br/>
<span class="itemtitle">Run towards the danger : confrontations with...</span><br/>
0890
</div>
<hr class="thinline"/>

<h2>CDBK</h2>

<div id="i5479" class="itemdiv">
    <span class="itemCallNum">CDBOOK 979.132 FEDARKO</span><br/>
<span class="itemtitle">The Emerald Mile [CD book] : the epic story...</span><br/>
5479
</div>
<hr class="thinline"/>

<div id="i2766" class="itemdiv">
    <span class="itemCallNum">CDBOOK EVANOVICH, J.</span><br/>
<span class="itemtitle">Game on [CD book] : tempting twenty-eight...</span><br/>
2766
</div>
<hr class="thinline"/>

<h2>DVD</h2>

<div id="i5457" class="itemdiv">
    <span class="itemCallNum">591.513 HOW</span><br/>
    <span class="itemtitle">How smart are animals? [widescreen DVD]</span><br/>
    5457
</div>
<hr class="thinline"/>

<div id="i3225" class="itemdiv">
    <span class="itemCallNum">808.02 WRITING</span><br/>
<span class="itemtitle">Writing creative nonfiction [DVD]</span><br/>
3225
</div>
<hr class="thinline"/> -->


</body>
</html>
