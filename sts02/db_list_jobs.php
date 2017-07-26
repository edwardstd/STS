<?php
  // list_jobs.php

  // adds a new job to the jobs table if the Update button was
  // clicked and if there is a code in the job name text box.
  // also creates a table using the name of the job that will contain the job's steps

  // generate some javascript to display the table name, identify this table to the form, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Jobs";
           document.getElementById("tbl_name").value = "jobs";
           document.getElementById("update_btn").tabIndex = "3";
         </script>';

  // get a database connection
  $dbc = open_db();

  // initialize an sql message
  $sql_msg = "<br />Transaction Completed";

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["name"]) > 0)
    {
      // add the new job to the car code table
      $sql = 'insert into jobs values ("' . $_GET['name'] . '", ';
      $sql .= '"' . $_GET['description'] . '")';
      $rs = mysqli_query($dbc, $sql);

      // create the new job step table
      $sql = 'create table `' . $_GET['name'] . '` ';
      $sql .='(step_number int primary key, station varchar(256), pickup char(1), setout char(1), remarks varchar(256))';

      if (!mysqli_query($dbc, $sql))
      {
        $sql_msg = "SQL error: " . mysqli_error($dbc) . " [" . $sql . "]";
      }
    }
  }

  // query the database for all of the jobs and display them in a table
  $sql = "select * from jobs order by name";
  $rs = mysqli_query($dbc, $sql);

  print '<table>
           <tr>
             <td><input id="name" name="name" type="text" tabindex="1"required></td>
             <td><textarea name="description" rows="8" cols="128" tabindex="2"></textarea></td>
           </tr>
           <tr>
              <th>Job Name</th><th>Description</th>
           </tr>';

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print '<tr>
               <td><a href="db_edit.php?tbl_name=jobs&obj_name=' . urlencode($row[0]) . '">' . $row[0] . '</td>
               <td>' . nl2br($row[1]) . '</td>
             </tr>';
    }
  }
  print "</table>";

  // display a database status message
  print $sql_msg;

  // generate a javascript line to set focus on the first text box
  print '<script>document.getElementById("name").focus();</script>';

?>
