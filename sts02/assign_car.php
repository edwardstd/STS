<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      td {border: 1px solid black; padding: 10px}
      td.number {text-align: center}
    </style>
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <br /><br />
    <a href="fill_orders.php?reporting_marks=&wbnbr=">Return to Fill Car Orders</a>
    <br />
    <h2>Simulation Operations</h2>
    <h3>Fill Car Orders</h3>
    Assign the desired car to the car order by clicking on it's radio button and then on the "Assign" button.<br /><br />
    If there aren't any cars available that meet the shipment requirements, a message to that effect will be displayed.
    <br /><br />
    
    <form method="get" action="fill_orders.php">

    <?php
      // bring in the function files
      require "open_db.php";
      require "drop_down_list_functions.php";

      // get a database connection
      $dbc = open_db();

      // find out which car order is to be filled
      for ($i=0; $i<$_GET['row_count']; $i++)
      {
        $button_name = 'fill' . $i;
        $wbnbr_name = 'wbnbr' . $i;

        if (isset($_GET[$button_name]))
        {
          // found it - get the car order requirements
          $sql = 'select car_orders.shipment,
                         shipments.description,
                         shipments.consignment,
                         shipments.car_code,
                         shipments.loading_location,
                         shipments.unloading_location, shipments.remarks,
                         locations.station
                  from car_orders, shipments, locations
                  where car_orders.waybill_number = "' . $_GET[$wbnbr_name] . '"
                    and car_orders.shipment = shipments.code
                    and locations.code = shipments.loading_location';

          $rs = mysqli_query($dbc, $sql);
          $row = mysqli_fetch_row($rs);

          if (mysqli_num_rows($rs) <= 0)
          {
            print 'Select error - SQL: ' . $sql;
          }

          $shipment = $row[0];
          $description = $row[1];
          $consignment = $row[2];
          $car_code = $row[3];
          $loading_location = $row[4];
          $unloading_location = $row[5];
          $shipment_remarks = $row[6];
          $loading_station = $row[7];

          // display the shipment information
          print '<table>
                   <tr>
                     <td><b>Shipment</b></td><td>' . $row[0] . '</td>
                     <td><b>Loading Station</b></td><td>' . $row[7] . '</td></tr>
                   <tr>
                     <td><b>Description</b></td><td>' . $row[1] . '</td>
                     <td><b>Loading Location</b></td><td>' . $row[4] . '</td></tr>
                   <tr>
                     <td><b>Consignment</b></td><td>' . $row[2] . '</td>
                     <td><b>Destination</b></td><td>' . $row[5] . '</td></tr>
                   <tr>
                     <td><b>Car Code</b></td><td>' . $row[3] . '</td>
                     <td><b>Shipment Remarks</b></td><td>' . $row[6] . '</td></tr>
                 </table>';

          // build a query to find all of the cars at the current station
          $sql = 'select cars.reporting_marks,
                         cars.current_location,
                         0 as pri,
                         cars.load_count,
                         cars.remarks
                  from cars
                  where cars.status = "Empty"
                    and cars.car_code like REPLACE("' . $car_code . '", "*", "%")
                    and cars.current_location in (select locations.code
                                                  from locations
                                                  where locations.station = "' . $loading_station . '")
                  order by pri, cars.load_count';

          $rs1 = mysqli_query($dbc, $sql);

          // build a query to find out if this shipper has prioritized empty search locations
          $sql = 'select cars.reporting_marks,
                         cars.current_location,
                         empty_locations.priority as pri,
                         cars.load_count,
                         cars.remarks
                  from cars, empty_locations
                  where cars.status = "Empty"
                    and cars.car_code like REPLACE("' . $car_code . '", "*", "%")
                    and cars.current_location = empty_locations.location
                    and empty_locations.shipment = "' . $shipment . '"
                    and cars.current_location not in (select locations.code
                                                      from locations
                                                      where locations.station = "' . $loading_station . '")
                  order by pri, cars.load_count';

          $rs2 = mysqli_query($dbc, $sql);

          // build a query to find all remaining eligible cars on the system
          $sql = 'select distinct cars.reporting_marks,
                         cars.current_location,
                         0 as pri,
                         cars.load_count,
                         cars.remarks
                  from cars, empty_locations
                  where cars.status = "Empty"
                    and cars.car_code like REPLACE("' . $car_code . '", "*", "%")
                    and cars.reporting_marks not in
                    (select cars.reporting_marks
                     from cars
                     where cars.status = "Empty"
                       and cars.car_code like REPLACE("' . $car_code . '", "*", "%")
                       and cars.current_location in (select locations.code
                                                   from locations
                                                   where locations.station = "' . $loading_station . '")
                     union
                     select cars.reporting_marks
                     from cars, empty_locations
                     where cars.status = "Empty"
                       and cars.car_code like REPLACE("' . $car_code . '", "*", "%")
                       and cars.current_location = empty_locations.location
                       and empty_locations.shipment = "' . $shipment . '"
                       and cars.current_location not in (select locations.code
                                                      from locations
                                                      where locations.station = "' . $loading_station . '")
                    )
                  order by pri, cars.load_count';

          $rs3 = mysqli_query($dbc, $sql);

          // check for no cars found
          $total_cars_found = mysqli_num_rows($rs1) + mysqli_num_rows($rs2) + mysqli_num_rows($rs3);

          if ($total_cars_found > 0)
          {
            // display number of cars found
            print '<br />' . $total_cars_found . ' eligible cars found<br />';

            // display the "Assign" button
            print '<br /><input type="submit" name="assign" value="Assign"><br /><br />';

            // insert a hidden field to pass the waybill number back to the fill_orders program
            print '<input type="hidden" name="wbnbr" value="' . $_GET[$wbnbr_name] . '">';

            // set the first car flag to true
            $first_car = true;

            // build the table listing the cars
            print '<table>';

            // headings
            print '<tr>
                     <th>Select</th>
                     <th>Reporting<br />Marks</th>
                     <th>Current<br />Location</th>
                     <th>Priority</th>
                     <th>Load<br />Count</th>
                     <th>Remarks</th>
                   </tr>';

            // cars at the same station as the shipper
            while($row1 = mysqli_fetch_array($rs1))
            {
              print '<tr>
                     <td style="text-align: center">';
              if ($first_car)
              {
                print '<input name="reporting_marks" type="radio" value="' . $row1[0] . '" checked>';
                $first_car = false;
              }
              else
              {
                print '<input name="reporting_marks" type="radio" value="' . $row1[0] . '">';
              }
              print '</td>
                     <td>' . $row1[0] . '</td>
                     <td>' . $row1[1] . '</td>
                     <td class="number">' . $row1[2] . '</td>
                     <td class="number">' . $row1[3] . '</td>
                     <td>' . $row1[4] . '</td>
                   </tr>'; 
            }

            // cars at prioritized locations
            while($row2 = mysqli_fetch_array($rs2))
            {
              print '<tr>
                     <td style="text-align: center">';
              if ($first_car)
              {
                print '<input name="reporting_marks" type="radio" value="' . $row2[0] . '" checked>';
                $first_car = false;
              }
              else
              {
                print '<input name="reporting_marks" type="radio" value="' . $row2[0] . '">';
              }
              print '</td>
                     <td>' . $row2[0] . '</td>
                     <td>' . $row2[1] . '</td>
                     <td class="number">' . $row2[2] . '</td>
                     <td class="number">' . $row2[3] . '</td>
                     <td>' . $row2[4] . '</td>
                     </tr>'; 
            }

            // cars somewhere on the system
            while($row3 = mysqli_fetch_array($rs3))
            {
              print '<tr>
                     <td style="text-align: center">';
              if ($first_car)
              {
                print '<input name="reporting_marks" type="radio" value="' . $row3[0] . '" checked>';
                $first_car = false;
              }
              else
              {
                print '<input name="reporting_marks" type="radio" value="' . $row3[0] . '">';
              }
              print '</td>
                     <td>' . $row3[0] . '</td>
                     <td>' . $row3[1] . '</td>
                     <td class="number">' . $row3[2] . '</td>
                     <td class="number">' . $row3[3] . '</td>
                     <td>' . $row3[4] . '</td>
                     </tr>'; 
            }
            print '</table>';
          }
          else
          {
            print '<br />No eligible cars found on the system';
          }
        }
      }
    ?>
    </form>
  </body>
</html>
