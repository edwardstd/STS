<?php
  // list_locations_codes.php

  // adds a new location to the locations table if the Update button was
  // clicked and if there is a code in the location code text box.

  // generate some javascript to display the table name, identify this table to the form, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Locations";
           document.getElementById("tbl_name").value = "locations";
           document.getElementById("update_btn").tabIndex = "6";
         </script>';

  // generate some javascript to enable sorting the html table by clicking on the column headings
  require "sort_html_table.php";

  // get a database connection
  $dbc = open_db();

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["code"]) > 0)
    {
      // add the new location to the locations table
      $sql = 'insert into locations values ("' . $_GET['code'] . '", ';
      $sql .= '"' . $_GET['station'] . '", ';
      $sql .= '"' . $_GET['track'] . '", ';
      $sql .= '"' . $_GET['spot'] . '", ';
      $sql .= '"' . $_GET['remarks'] . '")';
      $rs = mysqli_query($dbc, $sql);
    }
  }

  // query the database for all of the locations and display them in a table
  $sql = "select * from locations order by code";
  $rs = mysqli_query($dbc, $sql);

  print '<table id="loc_tbl">
           <tr>
             <td><input id="code" name="code" type="text" tabindex="1" required></td>
             <td>' . drop_down_stations("station", "") . '</td>
             <td><input name="track" type="text" tabindex="3"></td>
             <td><input name="spot" type="text" tabindex="4"></td>
             <td style="text-align: center;"><input name="remarks" type="text" tabindex="5" size="25"></td>
           </tr>
           <tr>
             <th onclick="sortTable(\'loc_tbl\', 0, 2);"><i>Location Code</i></th>
             <th onclick="sortTable(\'loc_tbl\', 1, 2);"><i>Station</i></th>
             <th>Track</th>
             <th>Spot</th>
             <th>Remarks</th>
           </tr>';

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print "<tr>";
      for ($i=0; $i<=4; $i++)
      {
        if ($i == 0)
        {
          print '<td><a href="db_edit.php?tbl_name=locations&obj_name=' . $row[$i] . '">' . $row[$i] . '</a></td>';
        }
        else
        {
          print "<td>" . $row[$i] . "</td>";
        }
      }
      print "</tr>";
    }
  }
  print "</table>";

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("code").focus();</script>';
?>
