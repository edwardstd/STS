<?php
  // list_routing.php

  // adds a new station code to the routing table if the Update button was
  // clicked and if there is a code in the station code text box.

  // generate some javascript to display the table name, identify this table to the form, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Stations and Routing Instructions";
           document.getElementById("tbl_name").value = "routing";
           document.getElementById("update_btn").tabIndex = "3";
         </script>';

  // get a database connection
  $dbc = open_db();

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["station_code"]) > 0)
    {
      // add the new station code to the routing table
      $sql = 'insert into routing values ("' . $_GET['station_code'] . '", ';
      $sql .= '"' . $_GET['instructions'] . '")';
      $rs = mysqli_query($dbc, $sql);
    }
  }

  // query the database for all of the station codes and display them in a table
  $sql = "select * from routing order by station";
  $rs = mysqli_query($dbc, $sql);

  print '<table>
           <tr>
             <td><input id="station_code" name="station_code" type="text" tabindex="1" required></td>
             <td><textarea name="instructions" type="text" tabindex="2" cols="100" rows="5"></textarea></td>
           </tr>
           <tr>
             <th>Station Code</th><th>Routing Instructions</th>
           </tr>';

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print '<tr>
               <td><a href="db_edit.php?tbl_name=routing&obj_name=' . $row[0] . '">' . $row[0] . '</a></td>
               <td>' . $row[1] . '</td>
             </tr>';
    }
  }
  print "</table>";

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("station_code").focus();</script>';

?>
