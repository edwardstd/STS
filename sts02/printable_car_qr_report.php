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
      require "../php-barcode/php-barcode.php";

      // set the parameters for the bar code
      $image_height = 30;
      $image_width = 300;
      $x = $image_width/2;  // barcode center
      $y = $image_height/2; // barcode center
      $bar_width = 2;       // barcode height in 1D ; not use in 2D
      $bar_height = 20;     // barcode height in 1D ; module size in 2D
      $angle = 0;           // rotation in degrees 
      $type = 'code39';     // bar code type

      // has the display button be clicked?
      if (isset($_GET["display_btn"]))
      {
        // get a database connection
        $dbc = open_db();

        // get the desired job name
        $station_name = $_GET["station_name"];

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

        if ($station_name != "All")
        {
          // if the selection is not "All", build a query to pull in the information about the cars at the selected station

          $sql = 'select cars.current_location,
                         cars.reporting_marks
                  from cars
                  where cars.current_location in (select code from locations where station = "' . $station_name . '")
                  order by cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the selected station's car report
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Car QR/Bar Code Report</h2>';
            print '<h3>Station: ' . $station_name . '</h3>';
            print 'Car QR/Bar Codes<br /><br />';

            print '<table style="font: normal 10px Verdana, Arial, sans-serif;">';
            print '<tr>
                     <th>Location</th><th>QR Code</th>
                   </tr>';
            while ($row = mysqli_fetch_array($rs))
            {
              // check for the desired code type
              if ($_GET["code_type"] == "qr")
              {
                $qr_file_name = "./qrcodes/" . $row[1] . ".png";

                // if the file already exists, delete it
                if (is_file($qr_file_name))
                {
                  unlink($qr_file_name);
                }

                // generate this car's qr code
                QRcode::png($row[1], $qr_file_name, 'M', 1.5, 1);

                // generate the table row
                print '<tr>
                       <td>' . $row[0] . '</td>
                       <td><img src="' . $qr_file_name . '" style="vertical-align: middle">&nbsp;&nbsp;' . $row[1] . '</td>
                       </tr>';
              }
              else
              {
                // create the basic image and fill it with white
                $im = imagecreatetruecolor($image_width, $image_height);
                $black = ImageColorAllocate($im,0x00,0x00,0x00);
                $white = ImageColorAllocate($im,0xff,0xff,0xff);
                imagefilledrectangle($im, 0, 0, $image_width, $image_height, $white);

                // add the bar code to the basic image
                $data = Barcode::gd($im, $black, $x, $y, $angle, $type, array("code"=>$row[1]), $bar_width, $bar_height);

                // add a black rectangle around the bar code based on the size of the code that was generated
/*
                $code_width = $data["width"];
                $code_height = $data["height"];
                $left_margin = ($image_width - $code_width)/2;
                $top_margin = ($image_height - $code_height)/2;
                $right_margin = $image_width - $left_margin;
                $bottom_margin = $image_height - $top_margin;
                imagerectangle($im, $left_margin-3, $top_margin-3, $right_margin+3, $bottom_margin+3, $black);
*/

                $bar_file_name = "./barcodes/" . $row[1] . ".png";

                // if the file already exists, delete it
                if (is_file($bar_file_name))
                {
                  unlink($bar_file_name);
                }

                imagepng($im, $bar_file_name);
                imagedestroy($im);
                print '<tr>
                       <td>' . $row[0] . '</td>
                       <td style="text-align:center">
                         <img src="' . $bar_file_name . '" style="vertical-align: middle"><br />' . $row[1] . '</td>
                       </tr>';
              }
            }
            print '</table>';
          }
          else
          {
            print "No cars found at " . $_GET["station_name"] . "<br />";
          }     
        }
        else
        {
          // generate a list of all cars at all stations, sorted by station

          $sql = 'select cars.current_location,
                         cars.reporting_marks
                  from cars
                  order by cars.current_location, cars.reporting_marks';

          $rs = mysqli_query($dbc, $sql);
          if (mysqli_num_rows($rs) > 0)
          {
            // build a table for the selected station's car report
            print '<h1>' . $rr_name . '</h1>';
            print '<h2>Car QR/Bar Code Report</h2>';
            print '<h3>Station: ' . $station_name . '</h3>';
            print 'Car QR/Bar Codes<br /><br />';

            print '<table style="font: normal 10px Verdana, Arial, sans-serif;">';
            print '<tr>
                     <th>Location</th><th>QR Code</th>
                   </tr>';

            $prev_row = "";
            $first_row = true;
            while ($row = mysqli_fetch_array($rs))
            {
              // if the location for this row is different than the previous row (and it's not the first row)
              // generate a blank row to separate the locations
              if (($row[0] != $prev_row) && (!$first_row))
              {
                print '<tr><td colspan="10"></td></tr>';
              }
              $prev_row = $row[0];
              $first_row = false;

              // check for the desired code type
              if ($_GET["code_type"] == "qr")
              {
                $qr_file_name = "./qrcodes/" . $row[1] . ".png";

                // if the file already exists, delete it
                if (is_file($qr_file_name))
                {
                  unlink($qr_file_name);
                }

                // generate this car's qr code
                QRcode::png($row[1], $qr_file_name, 'M', 1.5, 1);

                // generate the table row
                print '<tr>
                       <td>' . $row[0] . '</td>
                       <td><img src="' . $qr_file_name . '" style="vertical-align: middle">&nbsp;&nbsp;' . $row[1] . '</td>
                       </tr>';
              }
              else
              {
                // create the basic image and fill it with white
                $im = imagecreatetruecolor($image_width, $image_height);
                $black = ImageColorAllocate($im,0x00,0x00,0x00);
                $white = ImageColorAllocate($im,0xff,0xff,0xff);
                imagefilledrectangle($im, 0, 0, $image_width, $image_height, $white);

                // add the bar code to the basic image
                $data = Barcode::gd($im, $black, $x, $y, $angle, $type, array("code"=>$row[1]), $bar_width, $bar_height);

                // add a black rectangle around the bar code based on the size of the code that was generated
                $code_width = $data["width"];
                $code_height = $data["height"];
                $left_margin = ($image_width - $code_width)/2;
                $top_margin = ($image_height - $code_height)/2;
                $right_margin = $image_width - $left_margin;
                $bottom_margin = $image_height - $top_margin;
                imagerectangle($im, $left_margin-2, $top_margin-2, $right_margin+2, $bottom_margin+2, $black);

                $bar_file_name = "./barcodes/" . $row[1] . ".png";

                // if the file already exists, delete it
                if (is_file($bar_file_name))
                {
                  unlink($bar_file_name);
                }

                imagepng($im, $bar_file_name);
                imagedestroy($im);
                print '<tr>
                       <td>' . $row[0] . '</td>
                       <td style="text-align:center">
                         <img src="' . $bar_file_name . '" style="vertical-align: middle"><br />' . $row[1] . '</td>
                       </tr>';
              }
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
    <br /><a href="display_car_qr_report.php">Return to Display Car QR/Bar Code Report page</a>
  </body>
</html>
