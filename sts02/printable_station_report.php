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
    <?php
      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";

      // has the display button be clicked?
      if (isset($_GET["display_btn"]))
      {
        // get a database connection
        $dbc = open_db();

        // get the desired job name
        $station_name = $_GET["station_name"];

        // get the print width from the settings table
        $sql = 'select setting_value from settings where setting_name = "print_width"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $print_width = $row[0];

        // get the railroad name from the settings table
        $sql = 'select setting_value from settings where setting_name = "railroad_name"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $rr_name = $row[0];

        if ($station_name != "All")
        {
          // if the selection is not "All", build a query to pull in the information about the cars at the selected station

          $sql = 'select cars.current_location,
                         cars.reporting_marks,
                         cars.car_code,
                         cars.status, 
                         shipments.consignment,
                         shipments.loading_location,
                         shipments.unloading_location,
                         shipments.remarks,
                         car_orders.waybill_number,
                         car_orders.shipment
                  from cars
                  left join car_orders on car_orders.car = cars.reporting_marks
                  left join shipments on shipments.code = car_orders.shipment
                  where cars.current_location in (select code from locations where station = "' . $station_name . '")
                  order by cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the selected station's car report
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Station Car Report</h2>';
            print '<h3>Station: ' . $station_name . '</h3>';
            print 'Cars on Hand<br /><br />';

            print '<table style="font: normal 10px Verdana, Arial, sans-serif; width: ' . $print_width . ';">';
            print '<tr>
                     <th>Location</th>
                     <th>Reporting<br />Marks</th>
                     <th>Car<br />Code</th>
                     <th>Status</th>
                     <th>Consignment</th>
                     <th>Loading<br />Location</th>
                     <th>Unloading<br />Location</th>
                     <th>Remarks</th>
                   </tr>';
            while ($row = mysqli_fetch_array($rs))
            {
              // figure out each car's next stop and bold that destination
              $bold_loc = 99;
              if ($row[3] == "Ordered")
              {
                $bold_loc = 5;
              }
              elseif (($row[3] == "Loading") || ($row[3] == "Loaded"))
              {
                $bold_loc = 6;
              }

              print '<tr>';
              if (substr($row[8], 4, 1) == "E")
              {
                // if this car is hooked to a non-revenue waybill, display only the final destination
                print '<td>' . $row[0] . '</td>
                       <td>' . $row[1] . '</td>
                       <td>' . $row[2] . '</td>
                       <td>' . $row[3] . '</td>
                       <td>Non-Revenue</td>
                       <td>N/A</td>
                       <td><u>' . $row[6] . '</u></td>
                       <td>Repositioning</td>';
              }
              else
              {
                // otherwise, display the information normally
                for ($i=0; $i<8; $i++)
                {
                  if ($i == $bold_loc)
                  {
                    print '<td><u>' . $row[$i] . '</u></td>';
                  }
                  else
                  {
                    print '<td>' . $row[$i] . '</td>';
                  }
                }
              }
              print '</tr>';
            }
            print '</table>';
            print '<br />(If a car is enroute, the next destination in the route is underlined.)<br />';
          }
          else
          {
            print "No cars found at " . $_GET["station_name"] . "<br />";
          }     
        }
        else
        {
          // generate a list of all cars at all stations, sorted by station

          $sql = 'select cars.current_location,
                         cars.reporting_marks,
                         cars.car_code,
                         cars.status, 
                         shipments.consignment,
                         shipments.loading_location,
                         shipments.unloading_location,
                         shipments.remarks,
                         car_orders.waybill_number,
                         car_orders.shipment
                  from cars
                  left join car_orders on car_orders.car = cars.reporting_marks
                  left join shipments on shipments.code = car_orders.shipment
                  order by cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the selected station's car report
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Station Car Report</h2>';
            print '<h3>Station: ' . $station_name . '</h3>';
            print 'Cars on Hand<br /><br />';

            print '<table style="font: normal 10px Verdana, Arial, sans-serif; width: ' . $print_width . ';">';
            print '<tr>
                     <th>Location</th>
                     <th>Reporting<br />Marks</th>
                     <th>Car<br />Code</th>
                     <th>Status</th>
                     <th>Consignment</th>
                     <th>Loading<br />Location</th>
                     <th>Unloading<br />Location</th>
                     <th>Remarks</th>
                   </tr>';

            $prev_row = "";
            $first_row = true;
            while ($row = mysqli_fetch_array($rs))
            {
              // figure out each car's next stop and bold that destination
              $bold_loc = 99;
              if ($row[3] == "Ordered")
              {
                $bold_loc = 5;
              }
              elseif (($row[3] == "Loading") || ($row[3] == "Loaded"))
              {
                $bold_loc = 6;
              }

              // if the location for this row is different than the previous row (and it's not the first row)
              // generate a blank row to separate the locations
              if (($row[0] != $prev_row) && (!$first_row))
              {
                print '<tr><td colspan="10"></td></tr>';
              }
              $prev_row = $row[0];
              $first_row = false;

              print '<tr>';
              if (substr($row[8], 4, 1) == "E")
              {
                // if this car is hooked to a non-revenue waybill, display only the final destination
                print '<td>' . $row[0] . '</td>
                       <td>' . $row[1] . '</td>
                       <td>' . $row[2] . '</td>
                       <td>' . $row[3] . '</td>
                       <td>Non-Revenue</td>
                       <td>N/A</td>
                       <td><u>' . $row[9] . '</u></td>
                       <td>Repositioning</td>';
              }
              else
              {
                // otherwise, display the information normally
                for ($i=0; $i<8; $i++)
                {
                  if ($i == $bold_loc)
                  {
                    print '<td><u>' . $row[$i] . '</u></td>';
                  }
                  else
                  {
                    print '<td>' . $row[$i] . '</td>';
                  }
                }
              }
              print '</tr>';
            }
            print '</table>';
            print '<br />(If a car is enroute, the next destination in the route is underlined.)<br />';
          }
          else
          {
            print "No cars found on the system.<br />";
          }     
        }
      }
    ?>
    <br /><a href="display_station_report.php">Return to Display Station Car Report page</a>
  </body>
</html>
