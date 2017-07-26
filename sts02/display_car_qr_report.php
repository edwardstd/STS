<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      td {border: 1px solid black; padding: 10px}
    </style>
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Reports</h2>
    <h3>Car QR/Bar Code Report</h3>
    <!-- this form generates another page that is modified for better printing  -->
    <!-- the user needs to use the browser's back button to return to this page -->
    <!-- or user the link displayed at the bottom of the last page              -->
    <form action="printable_car_qr_report.php" method="get"> 
    Select the desired station, the desired type of code, and then click the Display button.<br />
    <br />
    The codes for all of the cars at the selected station will be displayed on a page that is formatted for printing.<br />
    <br />
    Use the browser's "Back" button to return to this page or click on the link on the bottom of the last page of the report.
    <br /><br />
   

    <?php
      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";


      // generate a drop-down list of stations, radio buttons for the type of codes,  and the submit button
      print 'Station: ' . drop_down_stations("station_name", "") . '&nbsp;';

      print '<input name="code_type" type="radio" value="qr" checked> QR Code&nbsp;
             <input name="code_type" type="radio" value="bar"> Bar Code&nbsp;';

      print '<input name="display_btn" value="Display" type="submit" onclick="start_dots();"><br /><br >';
      print '<div id="dots" style="visibility: hidden">Working</div>';

      // generate some javascript that adds "All" to the top of the station drop-down list
      print '<script>
               var drop_down_list = document.getElementById("station_name");
               var option = document.createElement("option");
               option.text = "All";
               drop_down_list.add(option, drop_down_list[1]);
             </script>';

      // generate some javascript that will show a progress indicator until the printable page loads
      print '<script>
              function iterateDots()
              {
                var el = document.getElementById("dots");
                var dotsStr = el.innerHTML;
                var dotsLen = dotsStr.length;
                var maxDots = 10;
                el.innerHTML = (dotsLen < maxDots ? dotsStr + "." : "Working");
              }

              function start_dots()
              {
                var intervalMs = 300;
                var interval = setInterval("iterateDots()", intervalMs);
                document.getElementById("dots").style.visibility="visible";
              }
            </script>';
    ?>
    </form>
  </body>
</html>
