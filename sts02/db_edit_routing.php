<?php
  // edit_routing.php

  // edits the selected row in the stations and routing instructions table if the Update button was clicked
  // it replaces only those items where a new value was submitted

  // generate a javascript line to display the table name
  print '<script>document.getElementById("table_name").innerHTML = "Stations & Routing Instructions";</script>';
  
  // generate a javascript line to set the proper return link and text
  print '<script>document.getElementById("return_link").innerHTML = "Return to Stations & Routing Management page";</script>';
  print '<script>document.getElementById("return_link").href = "db_list.php?tbl_name=routing";</script>';

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
      // build a query to remove the selected station 
      $sql = 'delete from routing where station = "' . $_GET["obj_name"] . '"';
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg =  "<br />Delete Error: " . mysqli_error($dbc);
      }
      else
      {
        // if the delete was successful, return to the list_car_codes page
        header("Location: db_list.php?tbl_name=routing");
        exit();
      }
    }
    else
    {
      // this must be an update operation
      // build the update query based on the contents of the input text boxes
      $sql = "update routing set ";
      $first_field = true;

      // check each incoming input text box and add it to the update if there's something there
      if (strlen($_GET["obj_name"]) > 0)
      {
        $sql .= 'station = "' . $_GET["obj_name"] . '" ';
        $first_field = false;
      }

      if (strlen($_GET["instructions"]) > 0)
      {
        if (!$first_field)
        {
          $sql .= ", ";
        }
        $sql .= 'instructions = "' . $_GET["instructions"] . '" ';
        $first_field = false;
      }

      // run the update query if at least one field is to be updated
      if (!$first_field)
      {
        $sql .= 'where station = "' . urldecode($_GET["prev_obj_name"]) . '"';
        if (mysqli_query($dbc, $sql))
        {
          $sql_msg =  "<br />Transaction completed";
        }
        else
        {
          $sql_msg =  "<br />Update Error: " . mysqli_error($dbc);
        }
      }
    }
  }

  // generate a hidden field to send this form's table name to itself when it's refreshed
  print '<input id="tbl_name" name="tbl_name" value="routing" type="hidden">';

  // generate a hidden field to send this form's previous object name to itself when it's refreshed
  print '<input id="prev_obj_name" name="prev_obj_name" value="' . urlencode($obj_name) . '" type="hidden">';

  // query the database for the properties of the selected  car code and display them in a table
  $sql = 'select * from routing where station = "' . $obj_name . '"';
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
        <td>Station Code</td>
        <td>' . $row[0] . '</td>
        <td><input id="obj_name" name="obj_name" type="hidden" value="' . $obj_name . '"></td>
      </tr>
      <tr>
        <td>Routing Instructions</td>
        <td>' . nl2br($row[1]) . '</td>
        <td><textarea name="instructions" rows="5" cols="50"></textarea></td>
      </tr>
    </table>';

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("obj_name").focus();</script>';

  // display a status message
  print $sql_msg;

?>
