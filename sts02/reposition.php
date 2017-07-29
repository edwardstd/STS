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
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Simulation Operations</h2>
    <h3>Reposition Empty Cars</h3>
    Select a destination for each empty car that is to be repositioned and then click the Update button.<br />
    Leave the destination blank if the car is to remain at it's current location.<br /><br />
    <form method="get" action="reposition.php">
    <?php
      // this program displays all cars that have a status of "Empty" and aren not billed anywhere
      // it also creates empty car waybills for any cars where the user selects a destination

      // bring in the function files
      require "open_db.php";
      require "drop_down_list_functions.php";

      // generatge some javascript to enable sorting of the html table by clicking on the column headings
      require "sort_html_table.php";

      // open a database connection
      $dbc = open_db();

      // check to see if the Update button was clicked
      if (isset($_GET["update_btn"]))
      {
        // get the current operating session number, default to zero if the query returns nothing
        $sql = 'select setting_value from settings where setting_name = "session_nbr"';
        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          $row = mysqli_fetch_row($rs);
          $session_number = $row[0];
        }
        else
        {
          $session_number = 0;
        }

        // get the last waybill number generated, default to 1 if the query returns nothing
        $sql = 'select waybill_number from car_orders order by waybill_number desc limit 1';
        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          $row = mysqli_fetch_row($rs);
          $waybill_counter = substr($row[0], -2, 2) + 1;
        }
        else
        {
          $waybill_counter = 1;
        }

        // go through each of the rows from the incoming page and if a destination was selected from any car's drop-down list,
        // insert an empty car waybill into the waybills table
        for ($i=0; $i<$_GET["row_count"]; $i++)
        {
          // build the names of the input fields
          $list_name = "list" . $i;
          $car_name = "car" . $i;

          // construct the waybill number
          $wb_nbr = str_pad($session_number, 3, "0", STR_PAD_LEFT) . "-E" . str_pad($waybill_counter, 2, "0", STR_PAD_LEFT);

          if (strlen($_GET[$list_name]) > 0)
          {
            // build an sql query to create the empty car waybill
            $sql = 'insert into car_orders values ("' . $wb_nbr . '", "' . $_GET[$list_name] . '", "' . $_GET[$car_name] . '")';

            if (!mysqli_query($dbc, $sql))
            {
              print "Insert Error: " . mysqli_error($dbc) . " SQL: " . $sql;
            }

            // build an sql query to update the car's status to "Ordered"
            $sql = 'update cars set status = "Ordered" where reporting_marks = "' . $_GET[$car_name] . '"';

            if (!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql;
            }

            $waybill_counter++;
          }
        }
      }

      // build the sql query to pull in cars that have a status of "Empty-Available" and aren't billed
      $sql = 'select reporting_marks,
                     car_code,
                     current_location,
                     remarks
             from cars
             where status = "Empty"
             and not exists (select car_orders.car from car_orders where cars.reporting_marks = car_orders.car)';

      $rs = mysqli_query($dbc, $sql);

      // initialize a car counter
      $row_count = 0;

      // build the table of empty-available cars
      if (mysqli_num_rows($rs) > 0)
      {
        // generate the update button
        print '<input name="update_btn" value="Update" type="submit"><br /><br />';
        print '<table id="car_tbl" name="car_tbl">
                 <tr>
                   <th>Destination</th>
                   <th onclick="sortTable(\'car_tbl\', 1, 1);"><i>Reporting Marks</i></th>
                   <th onclick="sortTable(\'car_tbl\', 2, 1);"><i>Car Code</i></th>
                   <th onclick="sortTable(\'car_tbl\', 3, 1);"><i>Current Location</i></th>
                   <th>Remarks</th>
                 </tr>';
        while ($row = mysqli_fetch_array($rs))
        {
          print '<tr>
                 <td>' . drop_down_locations("list" . $row_count, 0) . '</td>
                 <td>' . $row[0] . '<input name="car' . $row_count . '" value="' . $row[0] . '" type="hidden"></td>
                 <td>' . $row[1] . '</td>
                 <td>' . $row[2] . '</td>
                 <td>' . $row[3] . '</td>
                 </tr>';
          $row_count++;
        }
        print '</table>';
        // put the row count into a hidden field for when this program calls itself
        print '<input name="row_count" value="' . $row_count . '" type="hidden">';
      }
      else
      {
        print "<br />No cars are currently available for repositioning.";
      }
    ?>
    </form>
  </body>
</html>
