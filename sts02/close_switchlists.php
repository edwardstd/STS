<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
  </head>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      td {border: 1px solid black; padding: 10px}
    </style>
  <body>
<div id="debug">
</div>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Simulation Operations</h2>
    <h3>Close Switchlists</h3>
    Select a job to close out</br /><br />
    <form action="close_switchlists.php" method="get">
    <?php
      // bring in the utility files
      require "open_db.php";
      require "drop_down_list_functions.php";

      // get a database connection
      $dbc = open_db();

      // was the Finish button clicked?
      if (isset($_GET["finish_btn"]))
      {
        // get the number of rows that were on the page
        $row_count = $_GET["row_count"];

        // update the current location of the cars as specified by the user
        for ($i=0; $i<$row_count; $i++)
        {
          // construct the list and car field names
          $list_name = "station_list" . $i;
          $car_name = "car" . $i;

          // does the drop-down list have a job name in it?
          if (strlen($_GET[$list_name]) > 0)
          {
            // build a query to update the car's current location field and remove the contents of it's "handled_by" field
            $sql = 'update cars
                    set current_location = "' . $_GET[$list_name] . '",
                        handled_by = ""
                    where reporting_marks = "' . $_GET[$car_name] . '"';

            if(!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . '<br />';
            }

            // build a query to update the car's status if it is at it's loading location
            $sql = 'update cars,
                           car_orders,
                           shipments
                    set cars.status = "Loading"
                    where cars.reporting_marks = "' . $_GET[$car_name] . '"
                      and cars.status = "Ordered" 
                      and car_orders.car = cars.reporting_marks
                      and car_orders.shipment = shipments.code
                      and cars.current_location = shipments.loading_location';

            if(!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . '<br />';
            }

            // build a query to update the car's status if it is at it's unloading location
            $sql = 'update cars,
                           car_orders,
                           shipments
                    set cars.status = "Unloading"
                    where cars.reporting_marks = "' . $_GET[$car_name] . '"
                      and cars.status = "Loaded" 
                      and car_orders.car = cars.reporting_marks
                      and car_orders.shipment = shipments.code
                      and cars.current_location = shipments.unloading_location';

            if(!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . '<br />';
            }

            // build a query to update the car's status if it is a non-revenue move and at it's destination 
            $sql = 'update cars,
                           car_orders
                    set cars.status = "Empty"
                    where car_orders.car = cars.reporting_marks
                      and car_orders.shipment not in (select code from shipments)
                      and cars.status = "Ordered"
                      and cars.current_location = car_orders.shipment
                      and cars.reporting_marks = "' . $_GET[$car_name] . '"';

            if(!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . '<br />';
            }
          }
        }       
      }

      // generate the list of jobs from which the user can choose
      print drop_down_jobs("job_list", "get_jobs_and_cars();");
      print '&nbsp;<input name="finish_btn" value="Finish" type="submit">';
      
    ?>
    <br /><br />
    <div id="instructions" style="visibility: hidden;">
    Mark where each car was left by selecting it's set-out location from it's drop-down list.<br />
    To update the current location of each car that was set out, click the Finish button.<br /><br />
    </div>
    <br />
    <div id="job_table_div">
      <!-- the guts of the table are filled in by the HttpRequest call-back function -->
    </div>
    </form>
  </body>

    <script>
      // this javascript routine makes an HttpRequest that provides a list of cars at the selected
      // station and each car will have a drop-down list of jobs that could add it to their pickup switchlist

      function get_jobs_and_cars()
      {
        // check to see if the job list has a job selected
        if (document.getElementById('job_list').value.length > 0)
        {
          // submit the request for the cars at the selected station
          var xmlhttp = new XMLHttpRequest();
          xmlhttp.onreadystatechange = function()
          {
            if (this.readyState == 4 && this.status == 200)
            {
               populate_job_table(this);
            }
          }
          var url = 'get_cars_in_job.php?job=' + encodeURI(document.getElementById('job_list').value);
          xmlhttp.open('GET', url, true);
          xmlhttp.send();
        }
      };

      // this is the call back function for the list of cars at the selected station
      function populate_job_table(xmlhttp)
      {
        if (xmlhttp.responseText == "None")
        {
          // tell the user that there aren't any cars in this job
          document.getElementById("job_table_div").innerHTML = "<tr><td>This switchlist doesn't contain any cars.</td></tr>";
        }
        else
        {
          // make the instruction block visible
          document.getElementById("instructions").style.visibility = "visible";

          // display the table being returned from the server
          document.getElementById("job_table_div").innerHTML = xmlhttp.responseText;
        }
      }

    </script>

</html>
