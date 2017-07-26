<?php
  // this routine returns a list of cars at a specified station to the calling HttpRequest

  // get a database connection
  require "open_db.php";
  $dbc = open_db();

  // get the incoming parameter
  $station = urldecode($_REQUEST["station"]);

  // build a query to get the routing instructions for this station
  $sql = 'select instructions from routing where station = "' . $station . '"';
  $rs = mysqli_query($dbc, $sql);
  $row = mysqli_fetch_row($rs);
  if (strlen($row[0]) > 0)
  {
    $instructions = "Routing Instructions: " . $row[0];
  }
  else
  {
    $instructions = "Routing Instruction: None";
  }

  // build a query to find all cars currently at the designated station
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
          where cars.current_location in (select code from locations where station = "' . $station . '")
            and cars.status in ("Ordered", "Loaded")
            and ((cars.handled_by is null) or (cars.handled_by = ""))
          order by cars.current_location';

  $rs = mysqli_query($dbc, $sql);

  // build a table (less the <table> and </table> tags) and return it as a string
  $row_count = 0;
  if (mysqli_num_rows($rs) > 0)
  {
    $data_table = '<table id="car_table">';
    $data_table .= '<tr><td colspan="8">' . nl2br($instructions) . '</td></tr>';
    $data_table .= '<tr>
                      <th>Select Job</th>
                      <th>Location</th>
                      <th>Reporting Marks</th>
                      <th>Car Code</th>
                      <th>Status</th>
                      <th>Consignment</th>
                      <th>Loading Location</th>
                      <th>Destination</th>
                    </tr>';

    while ($row = mysqli_fetch_array($rs))
    {
      // generate the table rows
      $data_table .= "<tr>";
      $data_table .= "<td>" . get_jobs_at_station($dbc, $station, $row_count) . "</td>";
      for ($i=0; $i<7; $i++)
      {
        if ($i == 1)
        // add a hidden field to the second column in each row
        {
          $data_table .= '<td>' . $row[$i] . '<input name="car' . $row_count . '"';
          $data_table = $data_table . ' value="' . $row[$i] . '" type="hidden"></td>';
        }
        elseif ($i == 4)
        {
          // if this is a non-revenue move, display "Non-Revenue", otherwise display the consignment
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td>Non-Revenue</td>';
          }
          else
          {
            $data_table .= '<td>' . $row[$i] . '</td>';
          }
        }
        elseif ($i == 5)
        {
          // build the loading location info
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td>N/A</td>';
          }
          else
          {
            // if this car is ordered, bold the the loading location
            if ($row[3] == "Ordered")
            {
              $data_table .= '<td><b>' . $row[$i] . '</b></td>';
            }
            else
            {
              $data_table .= '<td>' . $row[$i] . '</td>';
            }
          }
        }
        elseif ($i == 6)
        {
          // build the destination info
          if (substr($row[7], 4, 1) == "E")
          {
            $data_table .= '<td><b>' . $row[8] . '</b></td>';
          }
          else
          {
            // if this car is loaded, bold the the final destination
            if ($row[3] == "Loaded")
            {
              $data_table .= '<td><b>' . $row[$i] . '</b></td>';
            }
            else
            {
              $data_table .= '<td>' . $row[$i] . '</td>';
            }
          }
        }
        else
        {
          // if all else fails, display a normal row
          $data_table .= '<td>' . $row[$i] . '</td>';
        }
      }
      $data_table .= "</tr>";
      $row_count++;
    }
    $data_table .= "</table>";
    // add a hidden field to the end of the table containing the number of rows
    $data_table .= '<input name="row_count" value="' . $row_count . '" type="hidden">';
  }
  else
  {
    $data_table = "None";
  }
  print $data_table;

  // this function returns a drop-down list of jobs that pick up at this station
  function get_jobs_at_station($dbc, $station, $row_count)
  {
    // build a query to get the names of all of the jobs
    $sql = 'select name from jobs';
    $rs = mysqli_query($dbc, $sql);

    // build a drop-down list from the jobs that are set to pick up at this station
    if (mysqli_num_rows($rs))
    {
      $job_list = '<select name="job_list' . $row_count . '">';
      $job_list .= '<option value=""></option>';
      while ($row = mysqli_fetch_array($rs))
      {
        // build a query to see if this job is set to pick up at this station
        // if so, add it to the list of options
        $sql = 'select count(*) from `' . $row[0] . '` where station = "' . $station . '" and pickup = "T"';
        $rs_steps = mysqli_query($dbc, $sql);
        $row_steps = mysqli_fetch_row($rs_steps);
        if ($row_steps[0] > 0)
        {
          $job_list .= '<option value="' . $row[0] . '">' . $row[0] . '</option>';
        }
      }
      $job_list .= "</select>";
    }
    else
    {
      $job_list = "No job picks up here";
    }
    return $job_list;
  }
?>
