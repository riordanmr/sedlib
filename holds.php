<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type"content="text/html; charset=iso-8859-1">
<title>Onshelf Items</title>
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
      margin: 10% auto; /* from the top and centered */
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
      </div>

    </div>
    
    
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
<hr class="thinline"/>


</body>
</html>
