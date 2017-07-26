<?php
  // list_car_codes.php

  // adds a new car code to the car_codes table if the Update button was
  // clicked and if there is a code in the car code text box.

  // generate some javascript to display the table name, identify this table to the form, and set the update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Car Codes";
           document.getElementById("tbl_name").value = "car_codes";
           document.getElementById("update_btn").tabIndex = "4";
         </script>';

  // get a database connection
  $dbc = open_db();

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["car_code"]) > 0)
    {
      // add the new car code to the car code table
      $sql = 'insert into car_codes values ("' . $_GET['car_code'] . '", ';
      $sql .= '"' . $_GET['description'] . '", "' . $_GET['remarks'] . '")';
      $rs = mysqli_query($dbc, $sql);
    }
  }

  // query the database for all of the car codes and display them in a table
  $sql = "select * from car_codes order by code";
  $rs = mysqli_query($dbc, $sql);

  print '<table>
           <tr>
             <td><input id="car_code" name="car_code" type="text" tabindex="1" size="10" required></td>
             <td style="text-align: center;"><input name="description" type="text" tabindex="2" size="25"></td>
             <td style="text-align: center;"><input name="remarks" type="text" tabindex="3" size="25"></td>
           </tr>
           <tr>
             <th>Car Code</th><th>Description</th><th>Remarks</th>
           </tr>';

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      print '<tr>
               <td><a href="db_edit.php?tbl_name=car_codes&obj_name=' . $row[0] . '">' . $row[0] . '</a></td>
               <td>' . $row[1] . '</td>
               <td>' . $row[2] . '</td>
             </tr>';
    }
  }
  print "</table>";

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("car_code").focus();</script>';

?>
