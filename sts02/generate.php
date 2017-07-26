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
    <h3>Generate Car Orders</h3>
    Click on the Generate button to increment the current operating session number and generate car orders.<br /><br />

    <?php
      // this program generates car orders based on the low and high traffic settings for each shipper

      // bring in the function files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // bring in and display the current operating session number
      $sql = 'select setting_value from settings where setting_name = "session_nbr"';
      $rs = mysqli_query($dbc, $sql);
      if (mysqli_num_rows($rs) < 1)
      {
        print "Setting not found - Error: [" . mysqli_error($dbc) . "] SQL: " . $sql . "<br /><br />";
      }
      $row = mysqli_fetch_array($rs);
      $session_number = $row[0];

      // was the "Generate" button clicked?
      if (isset($_GET["generate_btn"]))
      {
        // increment the operating session number and store it in the settings table
        $session_number++;

        // display the new operating session number
        print "Generating car orders for  Operating Session " . $session_number . "</br><br >";

        $sql = 'update settings set setting_value = ' . $session_number . ' where setting_name = "session_nbr"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Setting not found - Error: [" . mysqli_error($dbc) . "] SQL: " . $sql . "<br /><br />";
        }

        // initialize a counter for the number of waybills generated this session
        $waybill_counter = 0;

        // go through the shippers and generate car orders as appropriate
        $sql = 'select * from shipments';
        $rs_shipments = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          while ($row = mysqli_fetch_array($rs_shipments))
          {
            // do the math
            $last_ship_date = $row[6];
            $min_interval = $row[7];
            $max_interval = $row[8];
            $min_amount = $row[9];
            $max_amount = $row[10];

            // find a random number between the min and max intervals and round any fraction either up or down
            $interval = round(mt_rand($min_interval * 100, $max_interval * 100)/100);

            // add the random number to the last ship date
            $ship_date = $last_ship_date + $interval;

            // is it time to ship?
            if ($ship_date <= $session_number)
            {
              // store this session number as the new last ship date
              $sql = 'update shipments set last_ship_date = ' . $session_number . ' where code = "' . $row[0] . '"';
              if (!mysqli_query($dbc, $sql))
              {
                print "Update Error: [" . mysqli_error($dbc) . "] SQL: " . $sql . "<br /><br />";
              }

              // determine the number of cars to order and round either up or down
              $num_cars = round(mt_rand($min_amount * 100, $max_amount * 100)/100);

              for ($i=0; $i<$num_cars; $i++)
              {
                // increment the waybill counter
                $waybill_counter++;

                // build the waybill number
                $wb_nbr = str_pad($session_number, 3, "0", STR_PAD_LEFT) . "-" . str_pad($waybill_counter, 3, "0", STR_PAD_LEFT);

                $sql = 'insert into car_orders (waybill_number, shipment) values ("' . $wb_nbr . '", "' . $row[0] . '")';
                if (!mysqli_query($dbc, $sql))
                {
                  print "Insert Error: [" . mysqli_error($dbc) . "] SQL: " . $sql . "<br /><br />";
                }
              }
            }
          }
          // display the number of car orders created
          print $waybill_counter . " car orders generated<br /><br />";
        }
      }
      else
      {
        print "Ready...<br /><br />";
      }
    ?>

    <form method="get" action="generate.php">
      <input name="generate_btn" value="Generate" type="submit"><br /><br />
    </form>
  </body>
</html>
