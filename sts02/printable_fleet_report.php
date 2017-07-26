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
      require "drop_down_list_functions.php";
      require "open_db.php";
      require "../phpqrcode/qrlib.php";

      // has the display button be clicked?
      if (isset($_GET["display_btn"]))
      {
        // get a database connection
        $dbc = open_db();

        // get the desired job name
        $car_code = $_GET["car_code"];
        $display_car_code = $car_code;

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

        if ($car_code != "All")
        {
          // if the selection is not "All", build a query to pull in the information about the specified car code
          // but first substitute % (SQL wild card) for any * in the car code
          $new_car_code = "";
          for ($i=0; $i<strlen($car_code); $i++)
          {
            if (substr($car_code, $i, 1) == '*')
            {
              $new_car_code = $new_car_code . '%';
            }
            else
            {
              $new_car_code = $new_car_code . substr($car_code, $i, 1);
            }
          }
          $car_code = $new_car_code;

          $sql = 'select cars.car_code, 
                         cars.current_location,
                         cars.reporting_marks
                  from cars
                  where cars.car_code like "' . $car_code . '"
                  order by cars.car_code, cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the cars, sorted by car type, location, and then reporting marks
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Car Fleet Management Report</h2>';
            print '<h3>Car Code: ' . $display_car_code . '</h3>';
            print 'Cars of this type and where they are located<br /><br />';

            print '<table style="font: normal 10px Verdana, Arial, sans-serif;">';
            print '<tr>
                     <th>Car Code</th><th>Location</th><th>Reporting Marks</th>
                   </tr>';

            $prev_row = "";
            $first_row = true;
            while ($row = mysqli_fetch_array($rs))
            {
              // if the car code for this row is different than the previous row (and it's not the first row)
              // generate a blank row to separate the car codes
              if (($row[0] != $prev_row) && (!$first_row))
              {
                print '<tr><td colspan="10"></td></tr>';
              }
              $prev_row = $row[0];
              $first_row = false;

              // generate the table row
              print '<tr>
                     <td>' . $row[0] . '</td>
                     <td>' . $row[1] . '</td>
                     <td>' . $row[2] . '</td>
                     </tr>';
            }
            print '</table>';
          }
          else
          {
            print "No cars of this type found<br />";
          }     
        }
        else
        {
          // generate a list of all cars sorted by car code, location, and reporting marks

          $sql = 'select cars.car_code,
                         cars.current_location,
                         cars.reporting_marks
                  from cars
                  order by cars.car_code, cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the report
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Car Fleet Management Report</h2>';
            print '<h3>Car Code: ' . $display_car_code . '</h3>';
            print 'Cars of this type and where they are located<br /><br />';


            print '<table style="font: normal 10px Verdana, Arial, sans-serif;">';
            print '<tr>
                     <th>Car Code</th><th>Location</th><th>Reporting Marks</th>
                   </tr>';

            $prev_row = "";
            $first_row = true;
            while ($row = mysqli_fetch_array($rs))
            {
              // if the car code for this row is different than the previous row (and it's not the first row)
              // generate a blank row to separate the car codes
              if (($row[0] != $prev_row) && (!$first_row))
              {
                print '<tr><td colspan="10"></td></tr>';
              }
              $prev_row = $row[0];
              $first_row = false;

              // generate the table row
              print '<tr>
                     <td>' . $row[0] . '</td>
                     <td>' . $row[1] . '</td>
                     <td>' . $row[2] . '</td>
                     </tr>';
            }
            print '</table>';
          }
          else
          {
            print "No cars found on the system.<br />";
          }     
        }
      }
    ?>
    <br /><a href="display_fleet_report.php">Return to Display Car Fleet Management Report page</a>
  </body>
</html>
