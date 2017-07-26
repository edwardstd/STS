<?php
  // list_car_orders.php

  // lists all car orders/waybills, the shipments that generated them, and the cars
  // assigned to them

  // generate some javascript to display the table name, identify this table to the program, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Car Orders";
           document.getElementById("tbl_name").value = "car_orders";
           document.getElementById("update_btn").tabIndex = "10";
         </script>';

  // generate some javascript to modify the instruction division
  print '
    <script>
      document.getElementById("instructions").innerHTML =
        "To cancel a car order, click on it\'s checkbox and then on the Update button.<br /><br />" +
        "Car orders that have already been filled cannot be cancelled.<br /><br />";
    </script>';

  // generate some javascript to enable the html table to be sorted by click on the column headers
  require 'sort_html_table.php';

  // get a database connection
  $dbc = open_db();

  // was the update button clicked?
  if (isset($_GET['update_btn']))
  {
    // go through the car orders and see if any of them were marked for cancellation
    $wb_count = $_GET['row_count'];
    for ($i=0; $i<$wb_count; $i++)
    {
      // check each checkbox's value
      $chkbox = "wb" . $i;
      if (strlen($_GET[$chkbox]) > 0)
      {
        // delete the car order
        $sql = 'delete from car_orders where waybill_number = "' . $_GET[$chkbox] . '"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br /><br />";
        }
      }
    }
  }


  // query the database for all of the car orderss and display them in a table
  $sql = 'select car_orders.waybill_number,
                 car_orders.car,
                 shipments.car_code,
                 car_orders.shipment,
                 shipments.consignment,
                 shipments.loading_location,
                 shipments.unloading_location,
                 cars.current_location,
                 cars.status,
                 shipments.remarks
          from car_orders
          left join shipments on shipments.code = car_orders.shipment
          left join cars on cars.reporting_marks = car_orders.car
          where car_orders.waybill_number like "%-E%"
          or (car_orders.waybill_number != "" or car_orders.waybill_number is not null)
          order by car_orders.waybill_number';

  $rs = mysqli_query($dbc, $sql);

  $row_count = 0;

  if (mysqli_num_rows($rs) > 0)
  {
    print
      '<table id="wb_tbl">
         <tr>
           <th>Cancel</th>
           <th onclick="sortTable(\'wb_tbl\', 0, 1);"><i>Waybill Number</i></th>
           <th onclick="sortTable(\'wb_tbl\', 1, 1);"><i>Assigned Car</i></th>
           <th onclick="sortTable(\'wb_tbl\', 2, 1);"><i>Car Code</i></th>
           <th onclick="sortTable(\'wb_tbl\', 3, 1);"><i>Shipment ID</i></th>
           <th onclick="sortTable(\'wb_tbl\', 4, 1);"><i>Consignment</i></th>
           <th onclick="sortTable(\'wb_tbl\', 5, 1);"><i>Status</i></th>
           <th onclick="sortTable(\'wb_tbl\', 6, 1);"><i>Loading Location</i></th>
           <th onclick="sortTable(\'wb_tbl\', 7, 1);"><i>Current Location</i></th>
           <th onclick="sortTable(\'wb_tbl\', 8, 1);"><i>Destination</i></th>
           <th onclick="sortTable(\'wb_tbl\', 9, 1);"><i>Remarks</i></th>
         </tr>';

    while ($row = mysqli_fetch_array($rs))
    {
      // if a car order is for a non-revenue move (reposition) find out about it's car
      if (substr($row[0], 4, 1) == "E")
      {
        $sql_car_code = 'select car_code from cars where reporting_marks = "' . $row[1] . '"';
        $rs_car_code = mysqli_query($dbc, $sql_car_code);
        $row_car_code = mysqli_fetch_row($rs_car_code);

        // generate the empty way bill row
        print '<tr>
                 <td style="text-align: center;">
                   <input name=wb' . $row_count . ' value="' . $row[0] . '" type="checkbox" disabled>
                 </td>
                 <td>' . $row[0] . '</td>
                 <td>' . $row[1] . '</td>
                 <td>' . $row_car_code[0] . '</td>
                 <td>Non-Revenue</td>
                 <td>N/A</td>
                 <td>' . $row[8] . '</td>
                 <td>N/A</td>
                 <td>' . $row[7] . '</td>
                 <td>' . $row[3] . '</td>
                 <td>Reposition empty car</td>
               </tr>';
      }
      else
      {
        // otherwise generate a normal row
        print '<tr>';

        // only display an enabled checkbox if a car hasn't been assigned to the car order
        // we don't want to cancel car orders / waybills if a shipment is enroute
        if (strlen($row[1]) > 0)
        {
          print '<td style="text-align: center;">
                   <input name=wb' . $row_count . ' value="' . $row[0] . '" type="checkbox" disabled>
                 </td>';
        }
        else
        {
          print '<td style="text-align: center;">
                   <input name=wb' . $row_count . ' value="' . $row[0] . '" type="checkbox">
                 </td>';
        }
          print '<td>' . $row[0] . '</td>
                 <td>' . $row[1] . '</td>
                 <td>' . $row[2] . '</td>
                 <td>' . $row[3] . '</td>
                 <td>' . $row[4] . '</td>
                 <td>' . $row[8] . '</td>
                 <td>' . $row[5] . '</td>
                 <td>' . $row[7] . '</td>
                 <td>' . $row[6] . '</td>
                 <td>' . $row[9] . '</td>
               </tr>';
      }
      $row_count++;
    }
    print "</table>";
  }
  else
  {
    print "<br /><br />No car orders on hand<br /><br />";
  }
  print '<input name="row_count" value="' . $row_count . '" type="hidden">';
?>
