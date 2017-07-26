<?php
  // list_cars_codes.php

  // adds a new car to the cars table if the Update button was
  // clicked and if there is something in the reporting marks text box.

  // generate some javascript to display the table name, identify this table to the form, and set the 
  //update button's tab index
  print '<script>
           document.getElementById("table_name").innerHTML = "Cars";
           document.getElementById("tbl_name").value = "cars";
           document.getElementById("update_btn").tabIndex = "6";
         </script>';

  // generatge some javascript to enable sorting of the html table by clicking on the column headings
  require "sort_html_table.php";

  // get a database connection
  $dbc = open_db();

  // has the submit button been clicked?
  if (isset($_GET["update_btn"]))
  {
    // yes, so check to see if there is anything in the input text boxes
    if (strlen($_GET["rptgmarks"]) > 0)
    {
      // build the insert query
      $sql = 'insert into cars values (';
      $sql .= '"' . $_GET['rptgmarks'] . '", ';
      $sql .= '"' . $_GET['car_code'] . '", ';
      $sql .= '"' . $_GET['current_location'] . '", ';
      $sql .= '"' . $_GET['status'] . '", ';
      $sql .= '"", ';
      $sql .= '"' . $_GET['remarks'] . '", ';
      $sql .= '0';
      $sql .=')';

      // run the insert operation
      if (!mysqli_query($dbc, $sql))
      {
        print "Insert Error: " . mysqli_error($dbc) . "<br /><br />";
      }
    }
  }

  //print '<table id="car_tbl" style="font: normal 12px Verdana, Arial, sans-serif;">  
  print '<table id="car_tbl">  
           <tr>  
             <td><input id="rptgmarks" name="rptgmarks" type="text" tabindex="1" size="10" required></td>
             <td>' . drop_down_car_codes('car_code', 2, 'no_wild') . '</td>
             <td>' . drop_down_locations('current_location', 3) . '</td>
             <td>' . drop_down_status('status', 4) . '</td>
             <td></td>
             <td></td>
             <td></td>
             <td></td>
             <td style="text-align: center";><input name="remarks" type="text" tabindex="5"></td>
             <td></td>
           </tr>
           <tr>
             <th onclick="sortTable(\'car_tbl\', 0, 2);"><i>Reporting<br />Marks</i></th>
             <th onclick="sortTable(\'car_tbl\', 1, 2);"><i>Car Code</i></th>
             <th onclick="sortTable(\'car_tbl\', 2, 2);"><i>Current<br />Location</i></th>
             <th onclick="sortTable(\'car_tbl\', 3, 2);"><i>Status</i></th>
             <th onclick="sortTable(\'car_tbl\', 4, 2);"><i>Handled By</i></th>
             <th onclick="sortTable(\'car_tbl\', 5, 2);"><i>Consignment</i></th>
             <th onclick="sortTable(\'car_tbl\', 6, 2);"><i>Loading<br />Location</i></th>
             <th onclick="sortTable(\'car_tbl\', 7, 2);"><i>Destination</i></th>
             <th onclick="sortTable(\'car_tbl\', 8, 2);"><i>Remarks</i></th>
             <th onclick="sortTable(\'car_tbl\', 9, 2);"><i>Load<br />Count</i></th>
           </tr>';

  // query the database for all of the cars (and associated info from waybills and shipments) and display them in a table
  $sql = "select 
          cars.reporting_marks, 
          cars.car_code, 
          cars.current_location, 
          cars.status, 
          cars.handled_by,
          shipments.consignment, 
          shipments.loading_location, 
          shipments.unloading_location, 
          cars.remarks, 
          cars.load_count, 
          car_orders.waybill_number, 
          car_orders.shipment
          from cars
          left join car_orders on car_orders.car = cars.reporting_marks
          left join shipments on car_orders.shipment = shipments.code
          order by cars.reporting_marks";

  $rs = mysqli_query($dbc, $sql);

  if (mysqli_num_rows($rs) > 0)
  {
    while ($row = mysqli_fetch_array($rs))
    {
      // figure out each car's next destination
      $bold_loc = "";
      if ($row[3] == "Ordered")
      {
        $bold_loc = 6;
      }
      elseif (($row[3] == "Loaded") || ($row[3] == "Loading"))
      {
        $bold_loc = 7;
      }

      // build the table rows
      print "<tr>";
      for ($i=0; $i<10; $i++)
      {
        if ($i == 0)
        {
          // put a link to the edit_cars routine in the first column
          print '<td><a href="db_edit.php?tbl_name=cars&obj_name=' . urlencode($row[$i]) . '">' . $row[$i] . '</td>';
        }
        elseif ($i == 5)
        {
          // for the consignment column, check to see if this is a non-revenue waybill
          if (substr($row[10], 4, 1) == "E")
          {
            // if so, display "Non-Revenue"
            print "<td>Non-Revenue</td>";
          }
          else
          {
            // otherwise display the normal consignment
            print "<td>" . $row[$i] . "</td>";
          }
        }
        elseif ($i == 6)
        {
          // loading location
          // if this is a non-revenue waybill, display N/A
          if (substr($row[10], 4, 1) == "E")
          {
            print "<td>N/A</td>";
          }
          else
          {
            // if this is not a non-revenue waybill, display the normal contents for the column
            if ($i == $bold_loc)
            {
              // display the car's next destination in bold text
              print "<td><b>" . $row[$i] . "<b></td>";
            }
            else
            {
              print "<td>" . $row[$i] . "</td>";
            }
          }
        }
        elseif ($i == 7)
        {
          // final destination
          //if this is a non-revenue waybill, display it's destination which is stored in it's shipment column
          if (substr($row[10], 4, 1) == "E")
          {
            print "<td><b>" . $row[11] . "<b></td>";
          }
          else
          {
            // display the normal contents for the column
            if ($i == $bold_loc)
            {
              // display the car's next destination in bold text
              print "<td><b>" . $row[$i] . "<b></td>";
            }
            else
            {
              print "<td>" . $row[$i] . "</td>";
            }
          }
        }
        else
        {
          // if all else fails, display a normal column
          if (is_numeric($row[$i]))
          {
            print '<td style="text-align: center">' . $row[$i] . '</td>';
          }
          else
          {
            print '<td>' . $row[$i] . '</td>';
          }
        }
      }
      print "</tr>";
    }
  }
  print "</table>";

  // generate a javascript line to set focus on the first input text box
  print '<script>document.getElementById("rptgmarks").focus();</script>';
?>
