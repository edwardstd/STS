<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top;}
      th {border: 1px solid black; padding: 10px;}
      td {border: 1px solid black; padding: 10px;}
      td.checkbox {text-align: center;}
    </style>

    <script>
      // this javascript function is triggered by the user changing the "All" checkbox
      function checkall()
      {
        var row_count = document.getElementById('car_table').rows.length-1;
        if (document.getElementById('check_all').checked == true)
        {
          for (var i=0; i < row_count; i++)
          {
            var checkbox_name = "check" + i.toString();
            document.getElementById(checkbox_name).checked = true;
          }
        }
        else
        {
          for (var i=0; i < row_count; i++)
          {
            var checkbox_name = "check" + i.toString();
            document.getElementById(checkbox_name).checked = false;
          }
        }
      }
    </script>

  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Simulation Operations</h2>
    <h3>Load and Unload Cars</h3>
    <div id="instructions">
    Click the checkbox for each car that is to be loaded, unloaded, or released for use (after a non-revenue move) and
    then click the "Update" button.
    </div>
    <form method="get" action="load_unload.php">
    <?php
      // this program displays all cars that are in the process of being loaded or unloaded and
      // updates all cars that have been checked off by the user

      // bring in the function files
      require "open_db.php";

      // open a database connection
      $dbc = open_db();

      // check to see if the Update button was clicked
      if (isset($_GET["update_btn"]))
      {
        // go through each of the rows from the incoming page and if it's checkbox was checked, update it's status
        for ($i=0; $i<$_GET["row_count"]; $i++)
        {
          $checkbox_name = "check" . $i;
          if (isset($_GET[$checkbox_name]))
          {
            // determine each car's new status
            $car_name = "car" . $i;
            $status_name = "status" . $i;

            if ($_GET[$status_name] == "Loading")
            {
              $new_status = "Loaded";
            }
            else if ($_GET[$status_name] == "Unloading")
            {
              $new_status = "Empty";

              // for the cars with this new status, delete the car orders linked to them
              $sql = 'delete from car_orders where car = "' . $_GET[$car_name]. '"';
              if (!mysqli_query($dbc, $sql))
              {
                print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
              }
            }
            else if ($_GET[$status_name] == "Empty")
            {
              $new_status = "Empty";
              // this case is for non-revenue moves, ie: repositioning empty cars
              // for the cars with this new status, delete the car orders linked to them
              $sql = 'delete from car_orders where car = "' . $_GET[$car_name]. '"';
              if (!mysqli_query($dbc, $sql))
              {
                print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
              }
            }

            // build an sql query to update each car's status
            $sql = 'update cars set status = "' . $new_status . '" where reporting_marks = "' . $_GET[$car_name] . '"';
            if (!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql;
            }
          }
        }
        
      }

      // build the sql query to pull in cars that are being loaded, unloaded, or enroute home and have
      // reached their home location
      $sql = 'select cars.reporting_marks,
                     cars.car_code,
                     cars.current_location,
                     cars.status,
                     shipments.consignment,
                     shipments.loading_location,
                     shipments.unloading_location,
                     car_orders.waybill_number,
                     car_orders.shipment
              from cars
              left join car_orders on car_orders.car = cars.reporting_marks
              left join shipments on shipments.code = car_orders.shipment
              where ((cars.status = "Loading")
                  or (cars.status = "Unloading")
                  or ((cars.status = "Empty") and (cars.current_location = car_orders.shipment)))
              order by cars.current_location, cars.reporting_marks';
      $rs = mysqli_query($dbc, $sql);

      // initialize a car counter
      $row_count = 0;

      // build the table of cars to be loaded or unloaded
      if (mysqli_num_rows($rs) > 0)
      {
        // generate the update button
        print '<br /><input name="update_btn" value="Update" type="submit"><br /><br />';
        print '<table id="car_table" name="car_table">
                 <tr>
                   <th>
                     Load/Unload<br /><hr />
                     Check All <input id="check_all" name="check_all" type="checkbox" onchange="checkall();"
                   </th>
                   <th>Current<br />Location</th>
                   <th>Reporting Marks</th>
                   <th>Car Code</th>
                   <th>Status</th>
                   <th>Consignment</th>
                   <th>Loading<br />Location</th>
                   <th>Destination</th>
                 </tr>';
        while ($row = mysqli_fetch_array($rs))
        {
          // look for non-revenue waybills
          if (substr($row[7], 4, 1) == "E")
          {
            $consignment = "Non-Revenue";
            $load_loc = "N/A";
            $unload_loc = $row[8];
          }
          else
          {
            $consignment = $row[4];
            $load_loc = $row[5];
            $unload_loc = $row[6];
          }

          $checkbox_name = "check" . $row_count;
          $car_name = "car" . $row_count;
          $status_name = "status" . $row_count;
          $unload_loc_name = "unload" . $row_count;
          print '<tr>
                 <td class="checkbox"}>
                   <input id="' . $checkbox_name . '" name="' . $checkbox_name . '" value="' . $row[0] . '" type="checkbox">
                 </td>
                 <td>' . $row[2] . '</td>
                 <td>' . $row[0] . '<input name="' . $car_name . '" value="' . $row[0] . '" type="hidden"></td>
                 <td>' . $row[1] . '</td>
                 <td>' . $row[3] . '<input name="' . $status_name . '" value="' . $row[3] . '" type="hidden"></td>
                 <td>' . $consignment . '</td>
                 <td>' . $load_loc . '</td>
                 <td>' . $unload_loc . '
                   <input name="' . $unload_loc_name . '" value="' . $unload_loc . '" type="hidden">
                 </td>
                 </tr>';
          $row_count++;
        }
        print '</table>';
        // put the row count into a hidden field for when this program calls itself
        print '<input name="row_count" value="' . $row_count . '" type="hidden">';
      }
      else
      {
        print '<script>document.getElementById("instructions").innerHTML = "";</script>';
        print "<br />There are no cars currently in the process of  being loaded or unloaded.";
      }
    ?>
    </form>
  </body>
</html>
