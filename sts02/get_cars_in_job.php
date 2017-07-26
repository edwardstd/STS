<?php
  // this routine returns a list of cars being handled by a specified job  to the calling HttpRequest

  // get a database connection
  require "open_db.php";
  $dbc = open_db();

  // get the incoming parameter
  $job = $_REQUEST["job"];

  // build a query to find all cars currently being handled by the specified job
  $sql = 'select cars.current_location,
                 cars.reporting_marks,
                 cars.car_code,
                 cars.status, 
                 shipments.consignment,
                 shipments.loading_location,
                 shipments.unloading_location,
                 car_orders.waybill_number,
                 car_orders.shipment
          from cars
          left join car_orders on car_orders.car = cars.reporting_marks
          left join shipments on shipments.code = car_orders.shipment
          where cars.handled_by = "' . $job . '"
          order by cars.current_location, cars.reporting_marks';

  $rs = mysqli_query($dbc, $sql);

  // build a table (less the <table> and </table> tags) and return it as a string
  $row_count = 0;
  if (mysqli_num_rows($rs) > 0)
  {
    $data_table = '<table id="job_table">';
    $data_table .= '<tr>
                     <th>Select Set-out<br />Location</th>
                     <th>Where<br />Picked Up</th>
                     <th>Reporting<br />Marks</th>
                     <th>Car Code</th>
                     <th>Status</th>
                     <th>Consignment</th>
                     <th>Loading<br />Location</th>
                     <th>Destination</th>
                   </tr>';
                   
    while ($row = mysqli_fetch_array($rs))
    {
      // generate the table rows
      $data_table .= "<tr>";
      $data_table .= "<td>" . get_job_setout_locations($dbc, $job, $row_count) . "</td>";

      for ($i=0; $i<8; $i++)
      {
        if ($i == 1)
        // add a hidden field to the first column in each row
        {
          $data_table .= '<td>' . $row[0] . '<input name="car' . $row_count . '" value="' . $row[$i] . '" type="hidden"></td>';
        }
        elseif (($i >= 2) && ($i <=4))
        {
          // if all else fails, display a normal row
          $data_table .= '<td>' . $row[$i-1] . '</td>';
        }
        elseif ($i == 5)
        {
          // if this is a non-revenue move, display Non-Revenue, otherwise display the consignment
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td>Non-Revenue</td>';
          }
          else
          {
            $data_table .= '<td>' . $row[4] . '</td>';
          }
        }
        elseif ($i == 6)
        {
          // if this is a non-revenue move, display N/A, otherwise display the loading location
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td>N/A</td>';
          }
          else
          {
            // if the car status is Ordered, mark the loading location with bold letters
            if ($row[3] == "Ordered")
            {
              $data_table .= '<td><b>' . $row[5] . '</b></td>';
            }
            else
            {
              $data_table .= '<td>' . $row[5] . '</td>';
            }
          }
        }
        elseif ($i == 7)
        {
          // if this is a non-revenue move, display it's final destination which is stored in the car order's shipment column
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td><b>' . $row[8] . '</b></td>';
          }
          else
          {
            // if the car status is Loaded, mark the loading location with bold letters
            if ($row[3] == "Loaded")
            {
              $data_table .= '<td><b>' . $row[6] . '</b></td>';
            }
            else
            {
              $data_table .= '<td>' . $row[6] . '</td>';
            }
          }
        }
      }
      $data_table .= "</tr>";
      $row_count++;
    }
    print "</table>";
    // add a hidden field to the end of the table containing the number of rows
    $data_table .= '<input name="row_count" value="' . $row_count . '" type="hidden">';
  }
  else
  {
    $data_table = "None";
  }
  print $data_table;

  ///////////////////////////////////////// return to calling HttpRequest //////////////////////////////////////

  // this function returns a drop-down list of locations where a car could be set out
  function get_job_setout_locations($dbc, $job, $row_count)
  {
    // build a query to get the stations where this job can set out cars
    $sql = 'select locations.code from locations, `' . $job . '`
            where locations.station = `' . $job .'`.station
            and setout = "T"';
    $rs = mysqli_query($dbc, $sql);

    if (mysqli_num_rows($rs) > 0)
    {
      $station_list = '<select name="station_list' . $row_count . '">';
      $station_list .= '<option value=""></option>';
      while ($row = mysqli_fetch_array($rs))
      {
        $station_list .= '<option value="' . $row[0] . '">' . $row[0] . '</option>';
      }
      $station_list .= "</select>";
    }
    else
    {
      $station_list = '<select name="station_list"><option value="">This job has no set-out locations</option></select>';
    }
    return $station_list;
  }
?>
