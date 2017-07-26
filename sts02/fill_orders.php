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
    <h2>Simulation Operations</h2>
    <h3>Fill Car Orders</h3>
    Select the car order to fill by clicking on the order's "Fill" button.
    All empty cars that fit the order's requirements will be displayed on a new page.</br><br />
    On the new page, assign the desired car to the car order by clicking on it's radio button and then
    on the "Assign" button.<br /><br />
    Empty cars that meet the shipment's car code requirement are displayed on the new page as follows:
    <ul>
      <li>Any empty cars of the correct type currently at the same station as the shipper will be listed first.
          If there are multiple eligible cars at the shipper's station, they will be sorted by least used first.
          (Lowest load count)</li>
      <li>If locations to be searched for empty cars have been prioritized for this shipment, cars at those locations will be 
          displayed next, sorted in order of location priority and then by least used first. (Lowest load count)</li>
      <li>Finally, all remaining eligible cars on the system will be displayed sorted by the by least used first.
          (Lowest load count)</li>
    </ul>
    If there aren't any cars available that meet the shipment requirements, a message to that effect will be displayed.
    <br /><br />
    
    <form>

    <?php
      // bring in the function files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // did we get here when a car was assigned to a car order?
      if (! is_null($_GET['reporting_marks']))
      {
        // assign the selected car to the specified car order
        $sql = 'update car_orders
                set car = "' . $_GET['reporting_marks'] . '"
                where waybill_number = "' . $_GET['wbnbr'] . '"';

        if (!mysqli_query($dbc, $sql))
        {
          print 'Update error: ' . mysqli_error($dbc) . ' SQL: ' . $sql . '<br /><br />';
        }

        // check to see if the car is at it's loading location
        $sql = 'select count(*)
                from cars, shipments, car_orders
                where car_orders.waybill_number = "' . $_GET['wbnbr'] . '"
                  and shipments.code = car_orders.shipment
                  and cars.reporting_marks = "' . $_GET['reporting_marks'] . '"
                  and cars.current_location = shipments.loading_location
                  and cars.status = "Empty"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);

        if ($row[0] > 0)
        {
          // if it's at it's loading location, mark the car as "Loaded" and increment it's load count by 1
          $sql = 'update cars
                  set status = "Loaded",
                      load_count = load_count + 1
                  where reporting_marks = "' . $_GET['reporting_marks'] . '"';
        }
        else
        {
          // otherwise, mark the assigned car as "Ordered" and increment it's load count by 1
          $sql = 'update cars
                  set status = "Ordered",
                      load_count = load_count + 1
                  where reporting_marks = "' . $_GET['reporting_marks'] . '"';
        }
        
        if (!mysqli_query($dbc, $sql))
        {
          print 'Update error: ' . mysqli_error($dbc) . ' SQL: ' . $sql . '<br /><br />';
        }
      }

      // pull in all of the car orders that do not have a car assigned
      $sql = 'select car_orders.waybill_number,
                     car_orders.shipment,
                     shipments.description,
                     shipments.consignment,
                     shipments.car_code, 
                     shipments.loading_location,
                     shipments.unloading_location,
                     shipments.remarks
              from car_orders, shipments
              where (car_orders.shipment = shipments.code
              and ((car_orders.car = "") or (car_orders.car is null)))
              order by car_orders.waybill_number';
      $rs = mysqli_query($dbc, $sql);

      // generate a table of the eligible car orders
      if (mysqli_num_rows($rs) > 0)
      {
        print '<table>
                <tr>
                  <th>Select<br />Car Order</th>
                  <th>Waybill Number</th>
                  <th>Shipment Code</th>
                  <th>Shipment Description</th>
                  <th>Consignment</th>
                  <th>Car Code</th>
                  <th>Loading Location</th>
                  <th>Destination</th>
                  <th>Remarks</th>
                </tr>';

        $row_count = 0;
        while ($row = mysqli_fetch_array($rs))
        {
          print '<tr>
                  <td style="text-align: center; vertical-align: middle;">
                    <input name="fill' . $row_count . '" type="submit" value="Fill" formmethod="get" formaction="assign_car.php">
                  </td>
                  <td>' . $row[0] . '<input name="wbnbr' . $row_count . '" type="hidden" value="' . $row[0] . '"</td>
                  <td>' . $row[1] . '</td>
                  <td>' . $row[2] . '</td>
                  <td>' . $row[3] . '</td>
                  <td>' . $row[4] . '</td>
                  <td>' . $row[5] . '</td>
                  <td>' . $row[6] . '</td>
                  <td>' . $row[7] . '</td>
                </tr>';
          $row_count++;
        }
        print '<input name="row_count" type="hidden" value="' . $row_count . '">';
      }
    ?>
    </form>
  </body>
</html>
