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

        // get the desired waybill number
        $waybill_number = $_GET["waybill_number"];

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

        // get the current operating session number from the settings table
        $sql = 'select setting_value from settings where setting_name = "session_nbr"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $os_number = $row[0];

        // check to see if this is an empty car move
        if (strpos($waybill_number, "E") > 0)
        {
          // if so, generate a query to pull in selected items of information and fill in with empty move info
          $sql = 'select car_orders.car,
                         "",
                         "",
                         "",
                         car_orders.shipment,
                         car_orders.shipment,
                         "",
                         cars.current_location,
                         cars.car_code
                  from car_orders,
                       cars
                  where car_orders.waybill_number = "' . $waybill_number . '"
                    and car_orders.car = cars.reporting_marks';
        }
        else
        {
          // if not, generate a query to pull in the normal waybill information
          $sql = 'select car_orders.car,
                         car_orders.shipment,
                         shipments.description,
                         shipments.consignment,
                         shipments.loading_location,
                         shipments.unloading_location,
                         shipments.remarks,
                         cars.current_location,
                         cars.car_code
                  from car_orders,
                       shipments,
                       cars
                  where car_orders.waybill_number = "' . $waybill_number . '"
                    and car_orders.shipment = shipments.code
                    and car_orders.car = cars.reporting_marks';
        }

        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $reporting_marks = $row[0];
        $shipment = $row[1];
        $description = $row[2];
        $consignment = $row[3];
        $from_loc = $row[4];
        $to_loc = $row[5];
        $remarks = $row[6];
        $current_loc = $row[7];
        $car_code = $row[8];

        // generate a query to bring in the empty car's current station, track, and spot
        $sql = 'select station, track, spot from locations where code = "' . $current_loc . '"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $empty_station = $row[0];
        $empty_track = $row[1];
        $empty_spot = $row[2];

        // generate a query to bring in the "from" station, track, and spot
        $sql = 'select station, track, spot from locations where code = "' . $from_loc . '"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $from_station = $row[0];
        $from_track = $row[1];
        $from_spot = $row[2];

        // generate a query to bring in the "to" station, track, and spot
        $sql = 'select station, track, spot from locations where code = "' . $to_loc . '"';
        $rs = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_row($rs);
        $to_station = $row[0];
        $to_track = $row[1];
        $to_spot = $row[2];

        // if the empty car station and the from station aren't the same, build an empty car waybill
        if ($empty_station != $from_station)
        {
          // build the printable waybill for the empty move
          print '<table style="width: ' . $print_width . ';">
                 <tr style="font: normal 15px Verdana, Arial, sans-serif;">
                   <td style="text-align: center;" colspan="2">
                     <h2 style="font-family: Times New Roman", Times, serif;">' . $rr_name . '</h2>
                     <h3>FREIGHT WAYBILL</h3>
                     <div style="font: normal 10px Verdana, Arial, sans-serif;">
                       TO BE USED FOR SINGLE CONSIGNMENTS, CARLOAD AND LESS CARLOAD
                     </div>
                   </td>
                 </tr>
                 <tr>
                   <td style="width: 50%;">
                     <table>
                       <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                         <td style="width: 50%; text-align: center;">
                           CAR INITIALS AND NUMBER<br /><br />' . $reporting_marks . '
                         </td>
                         <td style="width: 50%; text-align: center;">
                           KIND<br /><br />' . $car_code . '
                         </td>
                       </tr>
                     </table>
                   </td>
                   <td style="width: 50%;">
                     <table>
                       <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                         <td style="width: 50%; text-align: center;">
                           OPERATING SESSION No. <br /><br />' . $os_number . '
                         </td>
                         <td style="width: 50%; text-align: center;">
                           WAYBILL No. <br /><br />' . $waybill_number . '
                         </td>
                       </tr>
                     </table>
                   </td>
                 </tr>
                 <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                   <td>
                     TO ' . $from_loc . '<br />
                     STATION ' . $from_station . '<br />
                     TRACK ' . $from_track . '<br />
                     SPOT ' . $from_spot . '<br />
                   </td>
                   <td>
                     FROM ' . $current_loc . '<br />
                     STATION ' . $empty_station . '<br />
                     TRACK ' . $empty_track . '<br />
                     SPOT ' . $empty_spot . '<br />
                   </td>
                 </tr>
                 <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                   <td>
                     SPECIAL INSTRUCTIONS (Regarding Icing, Weighing, Etc.)<br /><br /><br /><br />
                   </td>
                   <td>
                     SHIPMENT
                   </td>
                 </tr>
                 <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                   <td colspan="2">
                     DESCRIPTION OF ARTICLES<br /><br />Empty Car Assignment
                   </td>
                 </tr>
                 </table>
                 <br />';
        }
        else if (($empty_station == $from_station) && ($current_loc != $from_loc))
        {
          // otherwise just print a note saying that the empty car should be moved from one location to another
          // at the same station
          print '<table style="width: ' . $print_width . ';">
                 <tr style="font: normal 15px Verdana, Arial, sans-serif;">
                   <td style="text-align: center;" colspan="2">
                     <h1 style="font-family: Times New Roman", Times, serif;">' . $rr_name . '</h1>
                     <h2>COMPANY MEMO</h2>
                   </td>
                 </tr>
                 <tr>
                   <td>
                     FROM:<br /><br /><hr />
                     TO C&E No.<br /><br /><hr />
                     OPERATING SESSION: ' . $os_number . '
                   </td>
                 </tr>
                   <td>
                     REPOSITION THE FOLLOWING EMPTY CAR FOR LOADING AT ' . $from_loc . '<br /><br />
                     CAR INITIALS AND NUMBER: ' . $reporting_marks . '<br /><br />
                     KIND: ' . $car_code . '<br /><br />
                     LOCATED AT: ' . $current_loc . '
                   </td>
                 <tr>
                 </tr>
                 </table>
                 <br />';
        }

        // if this is not a reposition empty car move, build the printable waybill for the loaded move
        if (strpos($waybill_number, "E") == 0)
        {
        print '<table style="width: ' . $print_width . ';">
               <tr style="font: normal 15px Verdana, Arial, sans-serif;">
                 <td style="text-align: center;" colspan="2">
                   <h2 style="font-family: Times New Roman", Times, serif;">' . $rr_name . '</h2>
                   <h3>FREIGHT WAYBILL</h3>
                   <div style="font: normal 10px Verdana, Arial, sans-serif;">
                     TO BE USED FOR SINGLE CONSIGNMENTS, CARLOAD AND LESS CARLOAD
                   </div>
                 </td>
               </tr>
               <tr>
                 <td style="width: 50%;">
                   <table>
                     <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                       <td style="width: 50%; text-align: center;">
                         CAR INITIALS AND NUMBER<br /><br />' . $reporting_marks . '
                       </td>
                       <td style="width: 50%; text-align: center;">
                         KIND<br /><br />' . $car_code . '
                       </td>
                     </tr>
                   </table>
                 </td>
                 <td style="width: 50%;">
                    <table>
                     <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                       <td style="width: 50%; text-align: center;">
                         OPERATING SESSION No. <br /><br />' . $os_number . '
                       </td>
                       <td style="width: 50%; text-align: center;">
                         WAYBILL No. <br /><br />' . $waybill_number . '
                       </td>
                     </tr>
                   </table>
                 </td>
               </tr>
               <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                 <td>
                   TO ' . $to_loc . '<br />
                   STATION ' . $to_station . '<br />
                   TRACK ' . $to_track . '<br />
                   SPOT ' . $to_spot . '<br />
                 </td>
                 <td>
                   FROM ' . $from_loc . '<br />
                   STATION ' . $from_station . '<br />
                   TRACK ' . $from_track . '<br />
                   SPOT ' . $from_spot . '<br />
                 </td>
               </tr>
               <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                 <td>
                   SPECIAL INSTRUCTIONS (Regarding Icing, Weighing, Etc.)<br /><br /><br /><br />
                 </td>
                 <td>
                   SHIPMENT ' . $shipment . '<br />' . $description . '
                 </td>
               </tr>
               <tr style="font: normal 10px Verdana, Arial, sans-serif;">
                 <td colspan="2">
                   DESCRIPTION OF ARTICLES<br /><br />' . $consignment . '
                 </td>
               </tr>
               </table>';
        }
      }
    ?>
    <br /><a href="display_waybill.php">Return to Display Waybill page</a>
  </body>
</html>
