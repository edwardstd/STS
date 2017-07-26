<?php
  // list_shipments_codes.php

  // adds a new shipment to the shipments table if the Update button was
  // clicked and if there is a code in the location code text box.

  // generate some javascript to display the table name, identify this table to the program, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Shipments";
           document.getElementById("tbl_name").value = "shipments";
           document.getElementById("update_btn").tabIndex = "12";
         </script>';

  // generate some javascript to enable sorting of the html table by clicking on the column headings
  require "sort_html_table.php";

  // get a database connection
  $dbc = open_db();

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["code"]) > 0)
    {
      // add the new shipment to the shipments table
      $sql = 'insert into shipments values ("' . $_GET['code'] . '", ';
      $sql .= '"' . $_GET['description'] . '", ';
      $sql .= '"' . $_GET['consignment'] . '", ';
      $sql .= '"' . $_GET['car_code'] . '", ';
      $sql .= '"' . $_GET['loading_location'] . '", ';
      $sql .= '"' . $_GET['unloading_location'] . '", ';
      $sql .= $_GET['last_ship_date'] . ', ';
      $sql .= $_GET['min_interval'] . ', ';
      $sql .= $_GET['max_interval'] . ', ';
      $sql .= $_GET['min_amount'] . ', ';
      $sql .= $_GET['max_amount'] . ', ';
      $sql .= '"' . $_GET['remarks'] . '")';
      $rs = mysqli_query($dbc, $sql);
    }
  }

  // query the database for all of the shipments and display them in a table
  $sql = "select * from shipments order by code";
  $rs = mysqli_query($dbc, $sql);

  print '<table id="ship_tbl" style="font: normal 12px Verdana, Arial, sans-serif;">
           <tr>
             <td><input id="code" name="code" type="text" tabindex="1" required></td>
             <td><input name="description" type="text" tabindex="2"></td>
             <td><input name="consignment" type="text" tabindex="3"></td>
             <td>' . drop_down_car_codes('car_code', 4, 'wild_ok') . '</td>
             <td>' . drop_down_locations('loading_location', 5) . '</td>
             <td>' . drop_down_locations('unloading_location', 6) . '</td>
             <td><input name="last_ship_date" type="text" size="5" tabindex="7"></td>
             <td><input name="min_interval" type="text" size="5" tabindex="8"></td>
             <td><input name="max_interval" type="text" size="5" tabindex="9"></td>
             <td><input name="min_amount" type="text" size="5" tabindex="10"></td>
             <td><input name="max_amount" type="text" size="5" tabindex="11"></td>
             <td><input name="remarks" type="text" tabindex="12"></td>
             <td></td>
           </tr>
           <tr>
             <th onclick="sortTable(\'ship_tbl\', 0, 2);"><i>Shipment<br />Code</i></th>
             <th onclick="sortTable(\'ship_tbl\', 1, 2);"><i>Description</i></th>
             <th onclick="sortTable(\'ship_tbl\', 2, 2);"><i>Consignment</i></th>
             <th onclick="sortTable(\'ship_tbl\', 3, 2);"><i>Car<br />Code</i></th>
             <th onclick="sortTable(\'ship_tbl\', 4, 2);"><i>Loading<br />Location</i></th>
             <th onclick="sortTable(\'ship_tbl\', 5, 2);"><i>Unloading<br />Location</i></th>
             <th onclick="sortTable(\'ship_tbl\', 6, 2);"><i>Last<br />Ship<br />Date</i></th>
             <th onclick="sortTable(\'ship_tbl\', 7, 2);"><i>Minimum<br />Interval</i></th>
             <th onclick="sortTable(\'ship_tbl\', 8, 2);"><i>Maximum<br />Interval</i></th>
             <th onclick="sortTable(\'ship_tbl\', 9, 2);"><i>Minimum<br />Amount</i></th>
             <th onclick="sortTable(\'ship_tbl\', 10, 2);"><i>Maximum<br />Amount</i></th>
             <th onclick="sortTable(\'ship_tbl\', 11, 2);"><i>Remarks</i></th>
             <th>Empty Location<br />Search Priority</th>
           </tr>';

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print "<tr>";
      for ($i=0; $i<13; $i++)
      {
        if ($i == 0)
        {
          print '<td><a href="db_edit.php?tbl_name=shipments&obj_name=' . urlencode($row[0]) . '">' . $row[$i] . '</td>';
        }
        elseif (($i >=6) and ($i <= 10))
        {
          print '<td style="text-align: center">' . $row[$i] . '</td>';
        }
        elseif ($i == 12)
        {
          print '<td><a href="db_edit.php?tbl_name=empty_locations&obj_name=' . urlencode($row[0]) . '">Add/Edit</td>';
        }
        else
        {
          print '<td>' . $row[$i] . '</td>';
        }
      }
      print "</tr>";
    }
  }
  print "</table>";

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("code").focus();</script>';
?>
