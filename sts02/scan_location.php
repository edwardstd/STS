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
  <body onload='document.getElementById("location_id").focus();'>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Database Management</h2>
    <h3>Scan Locations</h3>
    <div id="instructions">
    If the scanner is set to send a "Carriage Return" at the end of the scan,<br />
    it will automatically trigger the Scan button.<br /><br />
    To manually search for location information, enter the location's ID code<br />
    into the input box and then click the Scan button.<br /><br />
    </div>
    <form action="scan_location.php" method="get">
      <input id="location_id" name="location_id" type="text">&nbsp;
      <input name="scan_btn" value="Scan" type="submit" autofocus><br /><br />

    <?php
      // display everything we know about the location

      // was the scan button clicked?
      if (isset($_GET["scan_btn"]))
      {      
        // pull in the utility files
        require "open_db.php";

        // get a database connection
        $dbc = open_db();

        // build a query to bring in the info
        $sql = 'select * from locations
                where code = "' . $_GET["location_id"] . '"';

        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          $row = mysqli_fetch_row($rs);

          // build a table showing the information
          // add a link to db_edit_location.php
          $location_link = '<a href="db_edit.php?tbl_name=locations&obj_name=' . $row[0] . '">' . $row[0] . '</a>';

/*
          $shipment_link = '<a href="db_edit.php?tbl_name=shipments&obj_name=' . $row[5] . '">' . $row[5] . '</a>';
*/
          print '<table>
                 <tr><th>Property</th><th>Value</th></tr>
                 <tr><td>Location Code</td><td>' . $location_link . '</td>
                 <tr><td>Station</td><td>' . $row[1] . '</td>
                 <tr><td>Track</td><td>' . $row[2] . '</td>
                 <tr><td>Spot</td><td>' . $row[3] . '</td>
                 <tr><td>Remarks</td><td>' . $row[4] . '</td>
                 </table>';

          // find all of the cars at this location
          $location_code = $row[0];
          $sql = 'select * from cars
                  where cars.current_location = "' . $location_code . '"
                  order by cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            print '<br />Cars at this location:<br />';
            print '<table>';
            print '<tr>
                   <th>Reporting<br />Marks</th>
                   <th>Car<br />Type</th>
                   <th>Status</th>
                   </tr>';

            while ($row = mysqli_fetch_array($rs))
            {
              $car_link = '<a href="db_edit.php?tbl_name=cars&obj_name=' . $row[0] . '">' . $row[0] . '</a>';
              print '<tr>
                     <td>' . $car_link . '</td>
                     <td>' . $row[1] . '</td>
                     <td>' . $row[3] . '</td>
                     </tr>';
            }
            
            print '</table>';
          }
          else
          {
            print "<br />No cars have been found at this location.<br /><br />";
          }
        }
        else
        {
          print "<br />Location " . $_GET["location_id"] . " not found. Check spelling.";
        }
      }
    ?>
    </form>
  </body>
</html>


