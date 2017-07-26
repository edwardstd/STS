<?php
  // edit_jobs.php

  // edits the selected row in the jobs table if the Update button was clicked
  // it replaces only those items where a new value was submitted

  // it also edits the rows in the table associated with this job
  // rows can be added and updated; to remove a row it's index value is set to zerio

  // generate a javascript line to display the table name
  print '<script>document.getElementById("table_name").innerHTML = "Jobs";</script>';
  
  // generate a javascript line to set the proper return link and text
  print '<script>document.getElementById("return_link").innerHTML = "Return to Job Management page";</script>';
  print '<script>document.getElementById("return_link").href = "db_list.php?tbl_name=jobs";</script>';

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
      // build a query to remove the selected job
      $sql = 'delete from jobs where name = "' . $_GET["obj_name"] . '"';
      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg =  "<br />Delete Error: " . mysqli_error($dbc);
      }
      else
      {
        // if the delete was successful, remove this job's step table
        $sql = "drop table " . $GET["obj_name"];
        if (!mysqli_query($dbc, $sql))
        {
          $sql_msg = "<br />Drop Error: " . mysqli_error($dbc);
        }
        else
        {
        // if the drop was successful, return to the list_jobs page
          header("Location: db_list.php?tbl_name=jobs");
          exit();
        }
      }
    }
    else
    {
      // this must be an update operation
      // build the update query based on the contents of the input text boxes
      // first check the job name and description
      $sql = "update jobs set ";
      $first_field = true;

      // check each incoming input text box and add it to the update if there's something there
      if (strlen($_GET["obj_name"]) > 0)
      {
        $sql .= 'name = "' . $_GET["obj_name"] . '" ';
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

      // run the update query if at least one field is to be updated
      if (!$first_field)
      {
        $sql .= 'where name = "' . $_GET["prev_obj_name"] . '"';
        if (mysqli_query($dbc, $sql))
        {
          $sql_msg =  "<br />Transaction completed";
        }
        else
        {
          $sql_msg =  "<br />Update Error: " . mysqli_error($dbc);
        }
      }

      // next, check the step table
      if ($_GET["row_count"] > 0)
      {
        // first, delete all of this job's step from it's step table
        // then go through each of the existing steps coming in from the web page
        // insert any step that doesn't have a sequence number of zero
        // including anything in the input text boxes

        // delete the existing steps
        $sql = 'delete from `' . $_GET["obj_name"] . '`';
        if (!mysqli_query($dbc, $sql))
        {
          $sql_msg = "Delete error: " . mysqli_error($dbc);
        }
        else
        {
          // loop through the existing steps on the web page and put them back into this job's step table
          for ($i=0; $i<$_GET["row_count"]; $i++)
          {
            // construct the names of each of the input fields in this table row
            $step_nbr = "step" . $i;
            $station_nbr = "station" . $i;
            $pickup_nbr = "pickup" . $i;
            $setout_nbr = "setout" . $i;
            $step_rmks_nbr = "step_remarks" . $i;

            // if the step sequence number is 0, skip it (this deletes it from the list of steps)
           if ($_GET[$step_nbr] > 0)
           {
              // build the sql insert command
              $sql = 'insert into `' . $_GET["obj_name"] . '` ';
              $sql .= 'values (' . $_GET[$step_nbr] . ', ';
              $sql .= '"' . $_GET[$station_nbr] . '", ';
              if (isset($_GET[$pickup_nbr]))
              {
                $sql .= '"T", ';
              }
              else
              {
                $sql .= '"F", ';
              }
              if (isset($_GET[$setout_nbr]))
              {
                $sql .= '"T", ';
              }
              else
              {
                $sql .= '"F", ';
              }
              $sql .= '"' . $_GET[$step_rmks_nbr] . '")';
  
              if (!mysqli_query($dbc, $sql))
              {
                $sql_msg = "Step Insert Error: " . mysqli_error($dbc);
              }
            }
          }
        }
      }
      // finally check the input text boxes
      if (strlen($_GET["seq_nbr"]) > 0)
      {
        $sql = 'insert into `' . $_GET["obj_name"] . '` ';
        $sql .= 'values ("' . $_GET["seq_nbr"] . '", ';
        $sql .= '"' . $_GET["station"] . '", ';

        if (isset($_GET["pickup"]))
        {
          $sql .= '"T", ';
        }
        else
        {
          $sql .= '"F", ';
        }

        if (isset($_GET["setout"]))
        {
          $sql .= '"T", ';
        }
        else
        {
          $sql .= '"F", ';
        }

        $sql .= '"' . $_GET["step_remarks"] . '")';

        if (!mysqli_query($dbc, $sql))
        {
          $sql_msg = "Insert error: " . mysqli_error($dbc);
        }
      }
    }
  }

  // generate a hidden field to send this form's table name to itself when it's refreshed
  print '<input id="tbl_name" name="tbl_name" value="jobs" type="hidden">';

  // generate a hidden field to send this form's previous object name to itself when it's refreshed
  print '<input id="prev_obj_name" name="prev_obj_name" value="' . $obj_name . '" type="hidden">';

  // query the database for the properties of the selected job  and display them in a table
  $sql = 'select * from jobs where name = "' . $obj_name . '"';
  $rs = mysqli_query($dbc, $sql);
  $row = mysqli_fetch_array($rs);

  print
    '<table>
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>Job Name</td>
        <td>' . $row[0] . '<input id="obj_name" name="obj_name" type="hidden" value="' . $obj_name . '"></td>
      </tr>
      <tr>
        <td>Description</td>
        <td><textarea name="description" rows="8" cols="128" tabindex="2">' . $row[1] . '</textarea></td>
      </tr>
    </table>';

  // query the database for the steps in this job's step table
  $sql = 'select * from `' . $_GET["obj_name"] . '` order by step_number';
  $rs = mysqli_query($dbc, $sql);

  // save the row count in a hidden field for use later on
  print '<input id="row_count" name="row_count" value="' . mysqli_num_rows($rs) . '" type="hidden">';

  print "<br />Job Steps (To remove a step, set it's sequence number to 0 [Zero])<br /><br />";

  // generate a table of this job's steps
  print '<table>
           <tr>
             <th>Sequence<br />Number</th>
             <th>Station</th>
             <th>Set Out</th>
             <th>Pick Up</th>
             <th>Remarks</th>
           </tr>
           <tr>
           </tr>
             <td><input id="seq_nbr" name="seq_nbr" type="text" size="5"></td>
             <td>' . drop_down_stations("station", "") . '</td>
             <td style="text-align: center"><input id="setout" name="setout" value="T" type="checkbox"></td>
             <td style="text-align: center"><input id="pickup" name="pickup" value="T" type="checkbox"></td>
             <td><input id="step_remarks" name="step_remarks" type="text" size="64"></td>
           </tr>';

  // keep track of how many rows are on in the step table on the web page
  $row_count=0;

  // build a new table of step
  while ($row = mysqli_fetch_array($rs))
  {
    print '<tr>';
    print '<td><input name="step' . $row_count . '" type="text" value="' . $row[0] . '" size="5"></td>';
    print '<td>' . $row[1] . '<input name="station' . $row_count . '" type="hidden" value="' . $row[1] . '"></td>';
    print '<td style="text-align: center">';
    if ($row[3] == "T")
    {
      print '<input name="setout' . $row_count . '" value="T" type="checkbox" checked>';
    }
    else
    {
      print '<input name="setout' . $row_count . '" value="F" type="checkbox">';
    }
    print '</td>';
    print '<td style="text-align: center">';
    if ($row[2] == "T")
    {
      print '<input name="pickup' . $row_count . '" value="T" type="checkbox" checked>';
    }
    else
    {
      print '<input name="pickup' . $row_count . '" value="F" type="checkbox">';
    }
    print '</td>';
    print '<td><input name="step_remarks' . $row_count . '" type="text" value="' . $row[4] . '" size="80"></td>';
    print '</tr>';
    $row_count++;
  }
  print '</table>';

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("obj_name").focus();</script>';

  // display a status message
  print $sql_msg;

?>
