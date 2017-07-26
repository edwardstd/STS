<?php
  // edit_shipments.php

  // edits the selected row in the shipment table if the Update button was clicked
  // it replaces only those items where a new value was submitted

  // generate a javascript line to display the table name
  print '<script>document.getElementById("table_name").innerHTML = "Shipments";</script>';
  
  // generate a javascript line to set the proper return link and text
  print '<script>document.getElementById("return_link").innerHTML = "Return to Shipment Management page";</script>';
  print '<script>document.getElementById("return_link").href = "db_list.php?tbl_name=shipments";</script>';

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
      // build a query to remove the selected shipment
      $sql = 'delete from shipments where code = "' . $_GET["obj_name"] . '"';
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg = "<br />Delete Error: " . mysqli_error($dbc);
      }
      else
      {
        // if the delete was successful, return to the list_shipments page
        header("Location: db_list.php?tbl_name=shipments");
        exit();
      }
    }
    else
    {
      // this must be an update operation
      // build the update query based on the contents of the input text boxes
      $sql = "update shipments set ";
      $first_field = true;

      // check each incoming input text box and add it to the update if there's something there
      if (strlen($_GET["obj_name"]) > 0)
      {
        $sql .= 'code = "' . $_GET["obj_name"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["description"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'description = "' . $_GET["description"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["consignment"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'consignment = "' . $_GET["consignment"] . '" ';
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

      if (strlen($_GET["loading_location"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'loading_location = "' . $_GET["loading_location"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["unloading_location"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'unloading_location = "' . $_GET["unloading_location"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["last_ship_date"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'last_ship_date = ' . $_GET["last_ship_date"] . ' ';
        $first_field = false;
      }

      if (strlen($_GET["min_interval"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'min_interval = ' . $_GET["min_interval"] . ' ';
        $first_field = false;
      }

      if (strlen($_GET["max_interval"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'max_interval = ' . $_GET["max_interval"] . ' ';
        $first_field = false;
      }

      if (strlen($_GET["min_amount"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'min_amount = ' . $_GET["min_amount"] . ' ';
        $first_field = false;
      }

      if (strlen($_GET["max_amount"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'max_amount = ' . $_GET["max_amount"] . ' ';
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

      // run the update query if at least one field is to be updated
      if (!$first_field)
      {
        $sql .= 'where code = "' . urldecode($_GET["prev_obj_name"]) . '"';
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
  print '<input id="tbl_name" name="tbl_name" value="shipments" type="hidden">';

  // generate a hidden field to send this form's previous object name to itself when it's refreshed
  print '<input id="prev_obj_name" name="prev_obj_name" value="' . urlencode($obj_name) . '" type="hidden">';

  // query the database for the properties of the selected  car code and display them in a table
  $sql = 'select * from shipments where code = "' . $obj_name . '"'; 
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
        <td>Shipment Code</td>
        <td>' . $row[0] . '</td>
        <td><input id="obj_name" name="obj_name" type="hidden" value="' . $obj_name . '"></td>
      </tr>
      <tr>
        <td>Description</td>
        <td>' . $row[1] . '</td>
        <td><input name="description" type="text"></td>
      </tr>
      <tr>
        <td>Commodity</td>
        <td>' . $row[2] . '</td>
        <td><input name="consignment" type="text"></td>
      </tr>
      <tr>
        <td>Car Code</td>
        <td>' . $row[3] . '</td>
        <td>' . drop_down_car_codes("car_code", "", "wild_ok") . '</td>
      </tr>
      <tr>
        <td>Loading Location</td>
        <td>' . $row[4] . '</td>
        <td>' . drop_down_locations("loading_location", "") . '</td>
      </tr>
      <tr>
        <td>Unloading Location</td>
        <td>' . $row[5] . '</td>
        <td>' . drop_down_locations("unloading_location", "") . '</td>
      </tr>
      <tr>
        <td>Last Ship Date</td>
        <td>' . $row[6] . '</td>
        <td><input name="last_ship_date" type="text"></td>
      </tr>
      <tr>
        <td>Min Interval</td>
        <td>' . $row[7] . '</td>
        <td><input name="min_interval" type="text"></td>
      </tr>
      <tr>
        <td>Max Interval</td>
        <td>' . $row[8] . '</td>
        <td><input name="max_interval" type="text"></td>
      </tr>
      <tr>
        <td>Min Amount</td>
        <td>' . $row[9] . '</td>
        <td><input name="min_amount" type="text"></td>
      </tr>
      <tr>
        <td>Max Amount</td>
        <td>' . $row[10] . '</td>
        <td><input name="max_amount" type="text"></td>
      </tr>
      <tr>
        <td>Remarks</td>
        <td>' . $row[11] . '</td>
        <td><input name="remarks" type="text"></td>
      </tr>
    </table>';

  // get this shipment's prioritized empty locations, if any
  $sql = 'select priority, location from empty_locations where shipment = "' . $obj_name . '" order by priority';
  $rs = mysqli_query($dbc, $sql);
  if (mysqli_num_rows($rs) > 0)
  {
    $link = 'db_edit.php?tbl_name=empty_locations&obj_name=' . $obj_name;

    print '<br />';
    print 'Prioritized Empty Car Search Locations - 
           Click <a href="' . $link . '">here</a> to modify the priorities<br /><br />';
    print '<table>
           <tr>
           <th>Priority</th>
           <th>Location</th>
           </tr>';
    while ($row = mysqli_fetch_array($rs))
    {
      print '<tr>
               <td>' . $row[0] . '</td>
               <td>' . $row[1] . '</td>
             </tr>';
    }
    print '</table>';
  }
  else
  {
    print '<br />';
    print "This shipment does not have any prioritized empty car locations";
  }

  // display a status message
  print $sql_msg;

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("obj_name").focus();</script>';

?>
