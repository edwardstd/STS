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
  <body onload='document.getElementById("reporting_marks").focus();'>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Database Management</h2>
    <h3 >Scan Cars</h3>
    <div id="instructions">
    If the scanner is set to send a "Carriage Return" at the end of the scan,<br />
    it will automatically trigger the Scan button.<br /><br />
    To manually search for car information, enter the car's reporting marks<br />
    into the Reporting Marks input box and then click the Scan button.<br /><br />
    </div>
    <form action="scan_car.php" method="get">
      <input id="reporting_marks" name="reporting_marks" type="text">&nbsp;
      <input name="scan_btn" value="Scan" type="submit" autofocus><br /><br />

    <?php
      // display everything we know about the selected car

      // was the scan button clicked?
      if (isset($_GET["scan_btn"]))
      {      
        // pull in the utility files
        require "open_db.php";

        // get a database connection
        $dbc = open_db();

        // convert the incoming reporting marks to upper case (sometimes scanners don't decode things correctly)
        $reporting_marks = strtoupper($_GET['reporting_marks']);

        // build a query to bring in the info
        $sql = 'select cars.car_code,
                cars.current_location,
                cars.status,
                cars.handled_by,
                car_orders.waybill_number,
                shipments.code,
                shipments.consignment,
                shipments.loading_location,
                shipments.unloading_location,
                cars.remarks,
                cars.load_count
                from cars
                left join car_orders on car_orders.car = cars.reporting_marks
                left join shipments on shipments.code = car_orders.shipment
                where cars.reporting_marks = "' . $reporting_marks . '"';

        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          $row = mysqli_fetch_row($rs);

          // build a table showing the information
          // add links to edit_car.php, edit_shipment.php, and edit_location.php programs
          $car_link = '<a href="db_edit.php?tbl_name=cars&obj_name='
                    . $reporting_marks . '">'
                    . $reporting_marks . '</a>';
          $car_code_link = '<a href="db_edit.php?tbl_name=car_codes&obj_name=' . $row[0] . '">' . $row[0] . '</a>';
          $location_link = '<a href="db_edit.php?tbl_name=locations&obj_name=' . $row[1] . '">' . $row[1] . '</a>';
          $job_link = '<a href="db_edit.php?tbl_name=jobs&obj_name=' . $row[3] . '">' . $row[3] . '</a>';
          $shipment_link = '<a href="db_edit.php?tbl_name=shipments&obj_name=' . $row[5] . '">' . $row[5] . '</a>';

          print '<table>
                 <tr><th>Property</th><th>Value</th></tr>
                 <tr><td>Reporting Marks:</td><td>' . $car_link . '</td></tr>
                 <tr><td>Car Code:</td><td>' . $car_code_link . '</td></tr>
                 <tr><td>Current Location:</td><td>' . $location_link . '</td></tr>
                 <tr><td>Status:</td><td>' . $row[2] . '</td></tr>
                 <tr><td>Handled By:</td><td>' . $job_link . '</td></tr>
                 <tr><td>Waybill Number:</td><td>' . $row[4] . '</td></tr>
                 <tr><td>Shipment Code:</td><td>' . $shipment_link . '</td></tr>
                 <tr><td>Consignment:</td><td>' . $row[6] . '</td></tr>
                 <tr><td>Loading Location:</td><td>' . $row[7] . '</td></tr>
                 <tr><td>Unloading Location:</td><td>' . $row[8] . '</td></tr>
                 <tr><td>Remarks:</td><td>' . $row[9] . '</td></tr>
                 <tr><td>Load Count:</td><td>' . $row[10] . '</td></tr>
                 </table>';
        }
        else
        {
          print "Reporting Marks " . $reporting_marks . " not found. Check spelling.";
        }
      }
    ?>
    </form>
  </body>
</html>


