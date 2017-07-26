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
    <h3>Maximum Car Requirements Estimate</h3>

    <?php
      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      $sql = 'select setting_value from settings where setting_name = "print_width"';
      $rs = mysqli_query($dbc, $sql);
      $row_print_width= mysqli_fetch_array($rs);
      $print_width = $row_print_width[0];

      print '<div id="instructions" style="width: ' . $print_width . '">';
    ?>
    The report shows an estimate of how many cars of each type would be needed to fill the anticipated
    number of shipments over a period of 10 operating sessions if all shipments occurred at their
    minimum interval and with their maximum number of carloadings.<br /><br />
    This is a "worst case" prediction for a three day car cycle. If the car cycle is longer than that,
    the number of required cars will increase proportionately.
    </div>
    <br /><br />
    <?php

      // pull in the shipment car codes and a calculation of how many of each car type could be needed for 10 sessions
      $sql = 'select car_code,
                     sum(ceiling((10 / min_interval)/3) * max_amount) as car_count
              from shipments
              group by car_code
              order by car_code';
      $rs = mysqli_query($dbc, $sql);

      if (mysqli_num_rows($rs) > 0)
      {
        print '<table>
               <tr>
                 <th>Car Code</th>
                 <th>Cars required to handle<br />10 days of shipments</th>
                 <th>Cars of this type available</th>
                 <th>Total Cars of this type<br />in the system</th>
               </tr>';

        // loop through each car type
        while ($row = mysqli_fetch_array($rs))
        {
          // find out how many cars of the current type are on hand in the cars table and how many are available
          $car_code = "";
          for ($i=0; $i<strlen($row[0]); $i++)
          {
            if (substr($row[0], $i, 1) == '*')
            {
              $car_code = $car_code . '%';
            }
            else
            {
              $car_code = $car_code . substr($row[0], $i, 1);
            }
          }

          // count cars of this type in the system
          $sql = 'select count(0) from cars where car_code like "' . $car_code . '"';
          $rs_count = mysqli_query($dbc, $sql);
          $car_code_count_row = mysqli_fetch_row($rs_count);
          $car_code_count = $car_code_count_row[0];

          // count available cars of this type in the system
          $sql = 'select count(0) from cars where car_code like "' . $car_code . '" and status != "Unavailable"';
          $rs_avail = mysqli_query($dbc, $sql);
          $car_code_avail_row = mysqli_fetch_row($rs_avail);
          $car_code_avail = $car_code_avail_row[0];

          print '<tr>
                 <td>' . $row[0] . '</td>
                 <td class="numbers">' . ceil($row[1]) . '</td>
                 <td class="numbers">' . $car_code_avail . '</td>
                 <td class="numbers">' . $car_code_count . '</td>
                 </tr>';
        }
        print '</table>';
      }
      else
      {
        print "<br /><br />No shipments were found.";
      }
    ?>
  </body>
</html>
