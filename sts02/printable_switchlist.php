<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse; table-layout: fixed;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 3px}
      td {border: 1px solid black; padding: 3px}
    </style>
  </head>
  <body>
    <?php
      // generate some javascript to enable moving cars forward or backward in the consist
      print '<script>
               function move_row(cell, move)
               {
                 // incoming cell is the one containing the up or down arrow image
                 var row_num = cell.parentElement.rowIndex;

                 // get the collection of rows in the table
                 var rows = document.getElementById("consist").rows;

                 // make sure that we do not go above the top or below the bottom of the table
                 if ((move == 1) && (row_num < rows.length - 1) || ((move == -1) && (row_num > 1)))
                 {
                   // swap the rows
                   var old_row = rows[row_num].innerHTML;
                   var new_row = rows[row_num + move].innerHTML;
                   rows[row_num].innerHTML = new_row;
                   rows[row_num + move].innerHTML = old_row;
                 }
               }
             </script>';

      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";

      // has the display button be clicked?
      if (isset($_GET["display_btn"]))
      {
        // get a database connection
        $dbc = open_db();

        // get the desired job name
        $job_name = $_GET["job_name"];

        // get the print width from the settings table
        $sql = 'select setting_value from settings where setting_name = "print_width"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $print_width = $row[0];

        // get the railroad initials from the settings table
        $sql = 'select setting_value from settings where setting_name = "railroad_initials"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $rr_initials = $row[0];

        // build a query to pull in the job's description
        $sql = 'select description from jobs where name = "' . $job_name . '"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $job_desc = $row[0];

        // build a query to pull in the switchlist information

        $sql = 'select `' . $job_name . '`.step_number,
                       cars.current_location,
                       cars.reporting_marks,
                       cars.car_code,
                       cars.status,
                       shipments.consignment,
                       shipments.loading_location,
                       shipments.unloading_location,
                       car_orders.shipment
                from cars
                left join car_orders on car_orders.car = cars.reporting_marks
                left join shipments on shipments.code = car_orders.shipment
                left join locations on locations.code = cars.current_location
                left join `' . $job_name . '` on `' . $job_name . '`.station = locations.station
                where cars.handled_by = "' . $job_name . '"
                  and `' . $job_name . '`.pickup = "T"
                order by `' . $job_name . '`.step_number,
                         cars.current_location,
                         shipments.unloading_location,
                         cars.reporting_marks';

        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          // initialize the counters for loads and empties
          $loads = 0;
          $empties = 0;

          // set up two columns, left one for the switch list, right one for the job's description
          $units = substr($print_width, -2);
          $value = substr($print_width, 0, strlen($print_width) - 2);
          $col_width = (($value/2) - 0.125) . $units;

          print '<table>';
          print '<tr>';
          print '<td>';

          // generate the heading
          print '<h2 style="text-align: center;">' . $rr_initials . '</h2>';
          print '<h3 style="text-align: center;">Switchlist</h3>';

          // generate the train number and other header information
          print '<table style="font: normal 10px Verdana, Arial, sans-serif; width: ' . $col_width . ';">';
          print '<tr>';
          print '<td style="width: 50%"><b>Train: ' . $job_name . '</b><br /><br /><br /></td>';
          print '<td style="width: 50%"><b>Dpt (station/date/time)</b><br /><br /><br /></td>';
          print '</tr>';
          print '<tr>';
          print '<td style="width: 50%"><b>Engine:</b><br /><br /><br /></td>';
          print '<td style="width: 50%"><b>Arr (station/date/time)</b><br /><br /><br /></td>';
          print '</tr>';
          print '<tr>';
          print '<td style="width: 50%"><b>Engineer:</b><br /><br /><br /></td>';
          print '<td style="width: 50%"><b>Conductor:</b><br /><br /><br /></td>';
          print '</tr>';
          print '</table>';

          // build a table for the selected job's switchlist
          print '<table id="consist" style="font: normal 8px Verdana, Arial, sans-serif; width: ' . $col_width . ';">';
          print '<tr>
                   <th>Rptg<br />Marks</th>
                   <th style="width: 20px; text-align: center;">Car<br />Code</th>
                   <th style="width: 15px; text-align: center;">E/L</th>
                   <th>Contents</th>
                   <th>From</th>
                   <th>To</th>
                   <th style="width: 30px">Left At</th>
                   <th style="width: 22px">Chg<br />Pos</th>
                 </tr>';

          $row_num = 1;
          while ($row = mysqli_fetch_array($rs))
          {
            print '<tr>';
            print '<td>' . $row[2] . '</td>';
            print '<td style="text-align: center">' . substr($row[3], 0, 1) . '</td>';

            if (($row[4] == "Empty") || ($row[4] == "Ordered"))
            {
              print '<td style="text-align: center">E</td>';
            }
            elseif ($row[4] == "Loaded")
            {
              print '<td style="text-align: center">L</td>';
            }

            if ($row[4] == "Loaded")
            {
              print '<td>' . $row[5] . '</td>';
              $loads++;
            }
            else
            {
              print '<td>&nbsp;</td>';
              $empties++;
            }

            print '<td>' . $row[1] . '</td>';

            if (($row[4] == "Empty") || ($row[4] == "Ordered"))
            {
              // if the commodity column is empty, this is a non revenue move and the car's destination
              // is stored in the car order's shipment column
              if (strlen($row[5]) <= 0)
              {
                print '<td>' . $row[8]  . '</td>';
              }
              else
              {
                print '<td>' . $row[6]  . '</td>';
              }
            }
            elseif ($row[4] == "Loaded")
            {
              print '<td>' . $row[7]  . '</td>';
            }

            print '<td style="width: 50px"></td>';

            print '<td style="text-align:center;">
                   <img src="graphics/up_arrow.png" onclick="move_row(this.parentElement, -1);">
                   <br />
                   <img src="graphics/dn_arrow.png" onclick="move_row(this.parentElement, 1);">
                   </td>';
            print '</tr>';

            // increment the row number
            $row_num++;
          }
          print '</table>';

          // display the number of loads and empties
          $total_cars = $loads + $empties;
          print "<br />";
          print "Loads: " . $loads . "<br />";
          print "Empties: " . $empties . "<br />";
          print "Total cars: " . $total_cars;
          print '</td>';

          // build the right hand column
          print '<td style="padding: 10px">';

          // display the selected job's description
          print '<table style="width: ' . $col_width . '";>';
          print '<tr>';
          print '<td>
                 <h3>Job: ' . $job_name . '</h3>
                 Description: ' . nl2br($job_desc) . '
                 </td>';
          print '</tr>';
          print '</td>';
          print '</tr>';
          print '</table>';
        }
        else
        {
          print "No switchlist found for " . $_GET["job_name"] . "<br />";
        }
        print '</table>';
      }
    ?>
    <br /><a href="display_switchlist.php">Return to Display Switchlist page</a>
  </body>
</html>
