<?php
  // edit_cars.php

  // edits the selected row in the car table if the Update button was clicked
  // it replaces only those items where a new value was submitted

  // generate a javascript line to display the table name
  print '<script>document.getElementById("table_name").innerHTML = "Cars";</script>';
  
  // generate a javascript line to set the proper return link and text
  print '<script>document.getElementById("return_link").innerHTML = "Return to Car Management page";</script>';
  print '<script>document.getElementById("return_link").href = "db_list.php?tbl_name=cars";</script>';

  // generate a QR code for this car
  include "../phpqrcode/qrlib.php";
  QRcode::png($obj_name, './qrcodes/' . $obj_name . '.png', 'M', 1.5, 1);

  // pull in the bar code library
  require "../php-barcode/php-barcode.php";

  // set the parameters for the bar code
  $image_height = 30;
  $image_width = 300;
  $x = $image_width/2;  // barcode center
  $y = $image_height/2; // barcode center
  $bar_width = 2;       // barcode height in 1D ; not use in 2D
  $bar_height = 20;     // barcode height in 1D ; module size in 2D
  $angle = 0;           // rotation in degrees
  $type = 'code39';     // type of bar code

  // create the basic image and fill it with white
  $im = imagecreatetruecolor($image_width, $image_height);
  $black = ImageColorAllocate($im,0x00,0x00,0x00);
  $white = ImageColorAllocate($im,0xff,0xff,0xff);
  imagefilledrectangle($im, 0, 0, $image_width, $image_height, $white);

  // add the bar code to the basic image
  $data = Barcode::gd($im, $black, $x, $y, $angle, $type, array("code"=>$obj_name), $bar_width, $bar_height);
  $bar_file_name = "./barcodes/" . $obj_name . ".png";
  imagepng($im, $bar_file_name);
  imagedestroy($im);

  // get a database connection
  $dbc = open_db();

  // initiate a database response message
  $sql_msg = "<br />Transaction completed";

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // is this a remove operation?
    if ($_GET["update_remove_btn"] == "remove")
    {
      // build a query to remove the selected car
      $sql = 'delete from cars where reporting_marks = "' . $_GET["obj_name"] . '"';
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg = "<br />Delete Error: " . mysqli_error($dbc);
      }
      else
      {
        // if the delete was successful, return to the list_cars page
        header("Location: db_list.php?tbl_name=cars");
        exit();
      }
    }
    else
    {
      // this must be an update operation
      // build the update query based on the contents of the input text boxes
      $sql = "update cars set ";
      $first_field = true;

      // check each incoming input text box and add it to the update if there's something there
      if (strlen($_GET["obj_name"]) > 0)
      {
        $sql .= 'reporting_marks = "' . $_GET["obj_name"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["car_code"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'car_code = "' . $_GET["car_code"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["current_location"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'current_location = "' . $_GET["current_location"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["status"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'status = "' . $_GET["status"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["handled_by"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'handled_by = "' . $_GET["handled_by"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["remarks"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'remarks = "' . $_GET["remarks"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["load_count"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'load_count = "' . $_GET["load_count"] . '" ';
        $first_field = false;
      }

      // run the update query if at least one field is to be updated
      if (!$first_field)
      {
        $sql .= 'where reporting_marks = "' . urldecode($_GET["prev_obj_name"]) . '"';
        if (mysqli_query($dbc, $sql))
        {
          $sql_msg =  "<br />Transaction completed<br /><br />";
        }
        else
        {
          $sql_msg =  "<br />Update Error: " . mysqli_error($dbc);
        }
      }
    }
  }

  // generate a hidden field to send this form's table name to itself when it's refreshed
  print '<input id="tbl_name" name="tbl_name" value="cars" type="hidden">';

  // generate a hidden field to send this form's previous object name to itself when it's refreshed
  print '<input id="prev_obj_name" name="prev_obj_name" value="' . urlencode($obj_name) . '" type="hidden">';

  // query the database for the properties of the selected  car code and display them in a table
  $sql = 'select * from cars where reporting_marks = "' . $obj_name . '"'; 
  $rs = mysqli_query($dbc, $sql);
  $row = mysqli_fetch_array($rs);

  print
    '<table>
      <tr>
        <th>Property</th>
        <th>Current Value</th>
        <th>New Value</th>
      </tr>
      <tr>
        <td>Reporting marks</td>
        <td>' . $row[0] . '</td>
        <td><input id="obj_name" name="obj_name" type="hidden" value="' . $obj_name . '"></td>
      </tr>
      <tr>
        <td>Car Code</td>
        <td>' . $row[1] . '</td>
        <td>' . drop_down_car_codes('car_code', 1, 'no_wild') . '</td>
      </tr>
      <tr>
        <td>Current Location</td>
        <td>' . $row[2] . '</td>
        <td>' . drop_down_locations('current_location',2) . '</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>' . $row[3] . '</td>
        <td>' . drop_down_status('status', 3) . '</td>
      </tr>
      <tr>
        <td>Handled By</td>
        <td>' . $row[4] . '</td>
        <td>' . drop_down_jobs('handled_by', '') . '</td>
      </tr>
      <tr>
        <td>Remarks</td>
        <td>' . $row[5] . '</td>
        <td><input name="remarks" type="text" tabindex="5"></td>
      </tr>
      <tr>
        <td>Load Count</td>
        <td>' . $row[6] . '</td>
        <td><input name="load_count" type="text" tabindex="6"></td>
      </tr>
      <tr>
        <td>QR Code</td>
        <td style="font: normal 10px Verdana, Arial, sans-serif; text-align: center;">
          <img src="./qrcodes/' . $obj_name . '.png" style="vertical-align: middle">&nbsp;&nbsp;' . $obj_name . '</td> 
        <td></td>
      </tr>
      <tr>
        <td>Bar Code</td>
        <td style="font: normal 10px Verdana, Arial, sans-serif; text-align: center">
          <img src="' . $bar_file_name . '" style="vertical-align: middle"><br />' . $obj_name . '</td>
        <td></td>
      </tr>
    </table>';

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("obj_name").focus();</script>';

  // display a status message
  print $sql_msg;

?>
