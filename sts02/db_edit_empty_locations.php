<?php
  // edit_empty)locations.php

  // edits the selected shipment's empty location search priorities
  // it replaces only those items where a new value was submitted

  // generate a javascript line to display the table name
  print '<script>document.getElementById("table_name").innerHTML = "Empty Car Location Search Priorities";</script>';
  
  // generate some javascript to set the proper return link and text
  print '<script>document.getElementById("return_link").innerHTML = "Return to Shipment Management page";</script>';
  print '<script>document.getElementById("return_link").href = "db_list.php?tbl_name=shipments";</script>';

  // decode the incoming shipment id
  $obj_name = urldecode($_GET['obj_name']);
  if (strlen($obj_name) <= 0)
  {
    $obj_name = $_GET["prev_obj_name"];
  }

  // generate some javascript to hide the Update/Remove radio buttons since we don't need it for this page
  print '<script>document.getElementById("update_remove_btn").style.visibility = "hidden";</script>';

  // generate some javascript to replace the boilerplate instructions with proper ones for this page
  $instructions = "<h3>Shipment ID: " . $obj_name . "</h3>";
  $instructions .= "After adding locations or modify existing priorities, click the Update button.<br /><br />";
  $instructions .= "To remove a location from the list of locations being searched for empty<br />";
  $instructions .= "cars, set the priority to 0 (Zero) and click the Update button.";
  print '<script>document.getElementById("instructions").innerHTML = "' . $instructions . '";</script>';
         

  // get a database connection
  $dbc = open_db();

  // initiate a database response message
  $sql_msg = "<br />Transaction completed";

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // check the location priority table
    if ($_GET["row_count"] > 0)
    {
      // first, delete all of this shipment's search locations from the table
      // then go through each of the existing rows coming in from the web page
      // insert any row that doesn't have a sequence number of zero
      // including anything in the input text boxes

      // delete the existing rows
      $sql = 'delete from empty_locations where shipment = "' . $_GET["prev_obj_name"] . '"';
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg = "Delete error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br /><br />";
      }
      else
      {
        // loop through the existing steps on the web page and put them back into this job's step table
        for ($i=0; $i<$_GET["row_count"]; $i++)
        {
          // construct the names of each of the input fields in this table row
          $shipment_nbr = "shipment" . $i;
          $priority_nbr = "priority" . $i;
          $location_nbr = "location" . $i;

          // if the priority sequence number is 0, skip it (this deletes it from the list of steps)
         if ($_GET[$priority_nbr] > 0)
         {
            // build the sql insert command
            $sql = 'insert into empty_locations 
                    values ("' . $_GET[$shipment_nbr] . '", '
                               . $_GET[$priority_nbr] . ', "'
                               . $_GET[$location_nbr] . '")';

            if (!mysqli_query($dbc, $sql))
            {
              $sql_msg = "Insert Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br /><br />";
            }
          }
        }
      }
    }
    // if there's new priority and location information in the text boxes, insert that information as well
    if (strlen($_GET["new_priority"]) > 0)
    {
      // build the sql insert query
      $sql = 'insert into empty_locations
              values ("' . $_GET["prev_obj_name"] . '", ' . $_GET["new_priority"]  . ', "' .$_GET["new_location"] . '")';
                         
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg = "Insert error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br /><br />";
      }
    }
  }

  // generate a hidden field to send this form's table name to itself when it's refreshed
  print '<input id="tbl_name" name="tbl_name" value="empty_locations" type="hidden">';

  // generate a hidden field to send this form's previous object name to itself when it's refreshed
  print '<input id="prev_obj_name" name="prev_obj_name" value="' . $obj_name . '" type="hidden">';

  // set up input fields for new entries to the table and the column headings
  print '<table>
           <tr>
             <td><input name="new_priority" type="text"></td>
             <td>' . drop_down_locations('new_location', 2) . '</td>
           </tr>
           <tr>
             <th>Priority</th>
             <th>Location</th>
           </tr>';

  // query the database for the list of empty car location search priorities for this shipment
  $sql = 'select * from empty_locations where shipment = "' . $obj_name . '" order by priority';
  $rs = mysqli_query($dbc, $sql);

  // build the table of search locations and their priorities
  $row_count = 0;
  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print '<tr>
               <td>
                 <input name="shipment' . $row_count . '" type="hidden" value="' . $row[0] . '">
                 <input name="priority' . $row_count . '" type="text" value="' . $row[1] . '">
               </td>
               <td>
                 <input name="location' . $row_count . '" type="hidden" value="' . $row[2] . '">' . $row[2] . '
               </td>
             </tr>';
      $row_count++;
    }
    print '</table>';

    // store the number of rows in the table for future use
    print '<input name="row_count" id="row_count" type="hidden" value="' . $row_count . '">';
  }
  else
  {
  }

  // display a status message
  print $sql_msg;

  // generate a javascript line to set focus on the first input text box

?>
