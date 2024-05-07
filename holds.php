<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
    /* Use a larger font, only on mobile devices.  Thanks to https://habr.com/en/sandbox/163605/ */
    @media (pointer: coarse)  {
	    /* mobile device */
        body {
           font-size: 350%;
        }
    }

    /* Thanks to https://stackoverflow.com/questions/256811/how-do-you-create-non-scrolling-div-at-the-top-of-an-html-page-without-two-sets */
    body {
        /* Disable scrollbars and ensure that the body fills the window */
        overflow: hidden;
        width: 100%;
        height: 100%;
    }
    .toppanel {
        position: absolute; top: 0px; width:98%; height: 3em; bottom: 0;
        font-size: 72%;
    }
    .mainpanel {
        position: absolute; top: 60px; overflow: auto; width: 100%; bottom: 0;
    }
    .lookinglabel {

    }
    .foundlabel {
        color: #30c030;
    }
    .cantfindlabel {
        color: #8B8000;
    }
    .problemlabel {
        color: #ff3030;
    }
    .showhide {
        float: right; text-decoration: underline;
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
    body {
        font-family: Verdana;
    }
    h2 {
        margin-top: 0.3em;
        margin-bottom: 0.3em;
    }
    .sepline {
        color: darkblue;
        border-top: 4px solid;
        margin-top: 2pt; 
        margin-bottom: 2pt;
    }
    .thickline {
        border: none;
        height: 10px;
        background: black;
    }
    .thinline {
        height: 2px; 
        border: none; 
        background-color: #000;
        margin: 0px;
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
        background-color: #ffde75;
    }

    .problem {
        background-color: #ffc0c0;
    }

    .itemCallNum {
        color: "darkgray";
    }

    .notesinitem {
        font-family: Georgia; font-style: italic;
    }

    .idsmall {
        font-size: 50%;
    }
    .itemtitle {
        font-family: Georgia;
    }
    .barcodeinmodal {
        font-family: Menlo,monospace; color: darkblue;
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
        font-size: 175%;
        width: 80%;
        border-radius: 8px;
    }

    .buttonCurrent {
        /*border-width: medium;*/
        border-color: blue;
    }

    .notes {
        font-size: 100%;
    }

    .errormodal {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: lightred; /* Fallback color */
    }

    .errormodalcontent {
      background-color: #ffc0c0;
      margin: 5% auto; /* from the top and centered */
      padding: 1em;
      border: 8px solid #888;
      width: 95%; /* Could be more or less, depending on screen size */
    }

    .errorclosebutton {
        font-size: 100%;
    }
    
</style>
    <script src="dynamicallyAccessCSS.js"></script>
    <script>
        var modal; 
        var currentId;

        function Init() {
            for (elem of document.getElementsByTagName('div')) {
                if(elem.hasAttribute("class")) {
                    if(elem.getAttribute("class").includes("itemdiv")) {
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
            }

            modal = document.getElementById("myModal");
            
            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
              if (event.target == modal) {
                modal.style.display = "none";
              }
            }

            postItemStatus("zzz", "badstat", "");

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

        // Modify the classes of the buttons in the modal dialog that allows the
        // user to change the status of the currently-selected item:
        // highlight the button that corresponds to the item's current found status.
        function showCurrentStatusInModal() {
            // Current item className will be something like "itemdiv" or "itemdiv found".
            // Loop through all of the buttons in the modal. For each, if the current
            // item class matches the button, set its class to myButton buttonCurrent,
            // else set it to simply myButton.
            // alert(document.getElementById(currentId).className);
            var curClass = document.getElementById(currentId).className;
            var aryButtonIds = ["btnFound", "btnCantFind", "btnProblem", "btnLooking"];
            var aryItemClass = ["itemdiv found", "itemdiv cantfind", "itemdiv problem", "itemdiv"]
            for (let idx = 0; idx<aryButtonIds.length; idx++) {
                if(curClass == aryItemClass[idx]) {
                    document.getElementById(aryButtonIds[idx]).className = "myButton buttonCurrent";
                } else {
                    document.getElementById(aryButtonIds[idx]).className = "myButton";
                }
            }
        }

        // An item has been clicked on, so create a modal dialog containing
        // buttons to act on that item.
        function onItemClick(e) {
            var id = e.currentTarget.id;
            currentId = id;
            //alert("onItemClick for e " + getAllProperties(e));
            showCurrentStatusInModal();
            document.getElementById("myModal").style.display = "block";
            document.getElementById("myModalContent").style.display = "block";
            //alert("For id " + id + " we have " + getAllProperties(document.getElementById(id).firstChild.nextElementSibling));
            var callNum = document.getElementById(id).firstChild.nextElementSibling.innerText;
            var itemIdLast4 = id.substring(id.length-4);
            document.getElementById("promptItem").innerHTML = callNum + 
              " &nbsp; <span class='barcodeinmodal'>" + itemIdLast4 + "</span>";
            // Populate the dialog's notes element with the notes of the current item.
            var notesId = "note" + currentId.substring(1);
            document.getElementById("notes").value = document.getElementById(notesId).innerHTML;
            //modal.style.display = "block";
            //modalcontent.style.display = "block";
        }

        // Version of fetch that errors out if it takes too long.
        // From https://dmitripavlutin.com/timeout-fetch-request/
        async function fetchWithTimeout(resource, options = {}) {
            const { timeout = 6000 } = options;
            
            const controller = new AbortController();
            const id = setTimeout(() => controller.abort(), timeout);

            const response = await fetch(resource, {
                ...options,
                signal: controller.signal  
            });
            clearTimeout(id);

            return response;
        }

        function postItemStatus(itemIdIn, statusIn, notesIn) {
            // Create the JSON data to be sent in the request body
            var requestData = {
                itemId: itemIdIn,
                status: statusIn,
                notes: notesIn
            };

            // Make the REST request
            fetchWithTimeout('postitem.php', {
                method: 'POST',
                headers: {        
                    'Accept': 'application/json',        
                    'Content-Type': 'application/json',    
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                // Process the response data
                document.getElementById("lookingcount").innerHTML = data.looking;
                document.getElementById("foundcount").innerHTML = data.found;
                document.getElementById("cantfindcount").innerHTML = data.cantfind;
                document.getElementById("problemcount").innerHTML = data.problem;
                console.log(data);
            })
            .catch(error => {
                // Handle any errors
                console.error('Error:', error);
                displayErrorModal("Error for item " + itemIdIn + ": " + error);
            });
        }

        function updateItemStatus(status) {
            // Store new notes in the HTML for that item.
            var notesId = "note" + currentId.substring(1);
            document.getElementById(notesId).innerHTML = document.getElementById("notes").value;
            // Update database on server.
            postItemStatus(currentId.substr(1), status, document.getElementById("notes").value);
        }

        function markFound() {
            document.getElementById(currentId).className = "itemdiv found";
            updateItemStatus("found");
        }

        function markStillLooking() {
            document.getElementById(currentId).className = "itemdiv"; // Was "itemdiv stilllooking"
            updateItemStatus("");
        }

        function markCantFind() {
            //alert("currentId=" +currentId + " props: " + getAllProperties(document.getElementById(currentId)));
            document.getElementById(currentId).className = "itemdiv cantfind";
            updateItemStatus("cantfind");
        }

        function markProblem() {
            document.getElementById(currentId).className = "itemdiv problem";
            updateItemStatus("problem");
        }

        function hideModeInEffect() {
            var labelShowHide = document.getElementById('showhide').text;
            // If Hide mode is in effect, the control to toggle it will read "Show".
            return (labelShowHide == "Show");
        }

        // Iterate over all the section headers for item location
        // (e.g., BIO or DVD), hiding or displaying them as per the
        // current Show/Hide setting. We hide the header only if Hide 
        // mode is in effect, and all items in the section are "found".
        function setLocHeadersForShowOrHide() {
            var bHideMode = hideModeInEffect();
            var divAllItems = document.getElementById('allitems');
            const childNodes = divAllItems.children;
            var nodeHdr;
            var curHdrId = '';
            var nNotFound = 0;
            for (let i = 0; i < childNodes.length; i++) {
                const node = childNodes[i];
                var thisId = node.id;
                if(thisId.startsWith('hdr')) {
                    // Now that we've hit the end of a run of items in the same location,
                    // determine the visibility of the header for the *previous* location.
                    if(curHdrId != '') {
                        // This isn't the special case of the very first header.
                        if(0==nNotFound) {
                            nodeHdr.style.display = bHideMode ? 'none' : 'block';
                        }
                    }
                    msg = "";
                    nNotFound = 0;
                    nodeHdr = node;
                    curHdrId = thisId;
                } else if(thisId.startsWith('i')) {
                    if(!node.className.includes('found')) {
                        // Here's an item that was not found, so increment count.
                        // Actually, this could have been a boolean flag for whether
                        // *any* non-found items were in this run of items in a location.
                        nNotFound++;
                    }
                }
            }
            // Process last location header.
            if(0==nNotFound) {
                nodeHdr.style.display = bHideMode ? 'none' : 'block';
            }
        }

        // Perform final processing when the modal dialog for setting item status
        // is closing.
        function endModal() {
            closeModal();
            if(hideModeInEffect()) {
                setLocHeadersForShowOrHide();        
            }
        }

        function onClickShowHide() {
            // Toogle the show/hide status of found items.  We do this by
            // altering the CSS rule for found items.
            var labelShowHide = document.getElementById('showhide').text;
            if('Hide' == labelShowHide) {
                getCSSRule('.found').style.setProperty("display", "none", "important");
                labelShowHide = 'Show';
            } else {
                getCSSRule('.found').style.setProperty("display", "", "important");
                labelShowHide = 'Hide';
            }

            // Now show or hide the location headers of each section of consecutive
            // items with the same location. In "Hide" mode, we don't want to show
            // the header for a section with no non-found items.
            document.getElementById('showhide').text = labelShowHide;
            setLocHeadersForShowOrHide();
            //alert(msg);
        }

        // Display an error modal dialog with the given text.
        function displayErrorModal(text) {
            // Get the modal
            var modal = document.getElementById("errorModal");

            // Get the element where we will put the text
            var modalText = document.getElementById("errorModalText");

            // Put the text in the modal
            modalText.innerHTML = text;

            // Display the modal
            modal.style.display = "block";
        }

        // Close the error modal dialog.
        function closeErrorModal() {
            var modal = document.getElementById("errorModal");
            modal.style.display = "none";
        }
</script>
</head>

<body onload="Init();">
    <!-- Non-scrolling area at top, to show counts of items with different statuses, plus Show/Hide found items control. -->
    <div id="toppanel" class="toppanel">
        <span class="lookinglabel">Look:</span> <span id="lookingcount"></span> &thinsp;
        <span class="foundlabel">Found:</span> <span id="foundcount"></span> &thinsp;
        <span class="cantfindlabel">Can't:</span> <span id="cantfindcount"></span> &thinsp;
        <span class="problemlabel">Prob:</span> <span id="problemcount"></span>
        <span class="showhide"><a id="showhide" onclick="onClickShowHide();">Hide</a> </span>
        <hr class="thinline"/>
    </div>
    <div id="main" class="mainpanel">
    <!-- Modal dialog to prompt user what to do -->
    <div id="myModal" class="modal">

      <!-- Modal content -->
      <div id="myModalContent" class="modalcontent">
        <span onclick="closeModal();" class="close">&times;</span>
        <p id="promptItem">Some text in the Modal..</p>
        <p><button id="btnFound" class="myButton" onclick="markFound(); endModal();">Found</button></p>
        <p><button id="btnCantFind" class="myButton" onclick="markCantFind(); endModal();">Can't find</button></p>
        <p><button id="btnProblem" class="myButton" onclick="markProblem(); endModal();">Problem</button></p>
        <p><button id="btnLooking" class="myButton" onclick="markStillLooking(); endModal();">Still looking</button></p>
        <p><textarea id="notes" name="notes" class="notes" rows="3"></textarea></p>
      </div>

    </div>

    <!-- Error modal dialog -->
    <div id="errorModal" class="errormodal">
      <div class="errormodalcontent">
        <span id="errorModalText"></span>
        <button id="closeButton" class="errorclosebutton" onclick="closeErrorModal()">Close</button>
      </div>
    </div>

    <div id="allitems">
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
                    echo "\n<h2 id='hdr$curLoc'>$curLoc</h2>\n";
                }

                // Set the class of the item based on its status from the DB.
                // Check the DB values to prevent HTML injection (Cross Site Scripting).
                $itemclass = "itemdiv";
                if($status=="found" || $status == "cantfind" || $status == "problem") {
                    $itemclass = $itemclass . " " . $status; 
                }
                echo "<div id='i$itemId' class='$itemclass'>\n";
                echo "<span class='itemCallNum'>$callNum</span><br/>\n";
                echo "<span class='itemtitle'>$title</span><br/>\n";
                $itemIdSpecial = "<span class='idsmall'>" . substr($itemId, 0, strlen($itemId)-4) . "</span> " . substr($itemId, strlen($itemId)-4);
                echo "$itemIdSpecial";
                echo "<div id='note$itemId' class='notesinitem'>$notes</div>\n";
                echo "<hr class='sepline'/>\n";
                echo "</div>";
                $prevLoc = $curLoc;
            }
        }
    }

    function doMain() {
        $connection = connectToDb();
        listRecsWhere($connection, "curloc not like 'J%'");
        echo "<hr class='thickline'/>";
        listRecsWhere($connection, "curloc like 'J%'");
        $connection->close();
    }
    doMain();
    ?>
    </div>
</div>
</body>
</html>
