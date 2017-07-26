<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      th.vert_bottom {vertical-align: bottom}
      td {border: 1px solid black; padding: 10px}
      td.numbers {text-align: center}
    </style>
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Reports</h2>
    <h3>Car Loading Forecast</h3>

    <?php
      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // get the current operating session number and the printer width
      $sql = 'select setting_value from settings where setting_name = "session_nbr"';
      $rs = mysqli_query($dbc, $sql);
      $rs_session_nbr = mysqli_fetch_array($rs);

      $sql = 'select setting_value from settings where setting_name = "print_width"';
      $rs = mysqli_query($dbc, $sql);
      $row_print_width= mysqli_fetch_array($rs);
      $print_width = $row_print_width[0];

      print '<div id="instructions" style="width: ' . $print_width . '">';
    ?>
    The report shows an estimate of how many cars of each type will be loaded during the next 10 operating 
    sessions.<br /><br />
    As the random number generator is not predictable (on purpose) the actual number of shipments will vary
    from day to day but should remain somewhat stable from one ten day period to another.</br /><br />
    The shipments are arranged in order by car code. This provides an approximate forecast of car needs over the 
    next 10 operating sessions.<br /><br />
    Click on the <b>Refresh</b> button to generate another set of random numbers.<br /><br />
    </div>
    <form action="car_forecast.php" method="get">
    <input id="refresh_btn" name="refresh_btn" value="Refresh" type="submit">
    <br /><br />
    <?php

      // pull in the shipment descriptions and their min/max/remainder values
      $sql = 'select code,
                     description,
                     car_code,
                     last_ship_date,
                     min_interval,
                     max_interval,
                     min_amount,
                     max_amount
              from shipments order by car_code';
      $rs = mysqli_query($dbc, $sql);

      if (mysqli_num_rows($rs) > 0)
      {
        // set up 10 buckets to total the 10 columns
        $car_totals = array(0,0,0,0,0,0,0,0,0,0); // subtotals for each car type
        $col_totals = array(0,0,0,0,0,0,0,0,0,0); // grand total for all cars

        // start the table
        print '<table style="width: ' . $print_width . '">
               <tr>
               <th class="vert_bottom" rowspan="2">Car<br />Code</th>
               <th colspan="2">Shipment</th>
               <th colspan="10">Operating Sessions</th>
               <th class="vert_bottom" rowspan="2">Total Car<br />Loadings</th>
               </tr>';

        print '<tr>
               <th>Code</th><th>Description</th>';

        for ($i=0; $i<10; $i++)
        {
          $session_number = $rs_session_nbr[0] + $i; 
          print '<th>' . $session_number . '</th>';
        }

        print '</tr>';

        // set a flag to suppress a blank row before the first row is displayed
        $first_row = true;
        $prev_car_code = "";

        // go through all of the shipments
        while ($row = mysqli_fetch_array($rs))
        {
          // if a change in car type is detected, print the car subtotals and a blank row
          if (!$first_row && ($row[2] != $prev_car_code))
          {
            $car_grand_total = 0;
            print '<tr>';
            print '<td colspan="3">Number of cars of this type to be loaded</td>';
            for ($i=0; $i<10; $i++)
            {
              print '<td>' . $car_totals[$i] . '</td>';
              $car_grand_total = $car_grand_total + $car_totals[$i];
              $car_totals[$i] = 0;
            }
            print '<td class="numbers">' . $car_grand_total . '</td>';
            print '</tr>';
            print '<tr><td colspan="14"></td></tr>';
          }
          $first_row = false;
          $prev_car_code = $row[2];

          // display the car code and shipment information
          print '<tr><td>' . $row[2] . '</td><td>' . $row[0] . '</td><td>' . $row[1] . '</td>';
          $total = 0;
          $prev_ship_date = $row[3];
          for ($i=0; $i<10; $i++)
          {
            // do the math
            $min_interval = $row[4];
            $max_interval = $row[5];
            $min_amount = $row[6];
            $max_amount = $row[7];

            $interval = round(mt_rand($min_interval, $max_interval));

            if (($prev_ship_date + $interval) <= ($rs_session_nbr[0] + $i))
            {
              // if it's time to ship, calculate how many car loads
              $num_cars = round(mt_rand($min_amount, $max_amount));
              print '<td class="numbers">' . $num_cars . '</td>';

              // save the current session as the new last ship date
              $prev_ship_date = $rs_session_nbr[0] + $i;

              // keep track of total carloads for this row
              $total = $total + $num_cars;

              // add this row/column's total carloads into the subtotals and grandtotals
              $car_totals[$i] = $car_totals[$i] + $num_cars;
              $col_totals[$i] = $col_totals[$i] + $num_cars;
            }
            else
            {
              // if there's now shipment for this session, generate an emty cell
              print '<td class="numbers">0</td>';
            }
          }
          print '<td class="numbers">' . $total . '</td>';
          print '</tr>';
        }
        // display the subtotal information for the last car type listed
        $car_grand_total = 0;
        print '<tr>';
        print '<td colspan="3">Number of cars of this type to be loaded</td>';
        for ($i=0; $i<10; $i++)
        {
          print '<td>' . $car_totals[$i] . '</td>';
          $car_grand_total = $car_grand_total + $car_totals[$i];
          $car_totals[$i] = 0;
        }
        print '<td class="numbers">' . $car_grand_total . '</td>';
        print '</tr>';
        print '<tr><td colspan="14"></td></tr>';

        // display the total projected shipments for each column (day) and then close the table
        $grand_total = 0;
        print '<tr>';
        print '<td colspan="3"><b>GRAND TOTAL</b></td>';
        for ($i=0; $i<10; $i++)
        {
          print '<td><b>' . $col_totals[$i] . '</b></td>';
          $grand_total = $grand_total + $col_totals[$i];
        }
        print '<td class="numbers"><b>' . $grand_total . '</b></td>';
        print '</tr>';
        print '</table>';
      }
      else
      {
        print "<br /><br />No shipments were found.";
      }
    ?>
    </form>
  </body>
</html>
