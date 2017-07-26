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
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Simulation Operations</h2>
    <h3>Build Switchlists</h3>
    Select a station for the pick-up switchlist</br /><br />
    <form action="build_switchlists.php" method="get">
    <?php
      // bring in the utility files
      require "open_db.php";
      require "drop_down_list_functions.php";

      // get a database connection
      $dbc = open_db();

      // was the Build button clicked?
      if (isset($_GET["build_btn"]))
      {
        // get the number of rows that were on the page
        $row_count = $_GET["row_count"];

        // mark the cars selected for pickup by the user
        for ($i=0; $i<$row_count; $i++)
        {
          // construct the list and car field names
          $list_name = "job_list" . $i;
          $car_name = "car" . $i;

          // does the drop-down list have a job name in it?
          if (strlen($_GET[$list_name]) > 0)
          {
            // build a query to update the car's "handled_by" field
            $sql = 'update cars set handled_by = "' . $_GET[$list_name];
            $sql = $sql . '" where reporting_marks = "' . $_GET[$car_name] . '"';
            if(!mysqli_query($dbc, $sql))
            {
              print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql;
            }
          }
        }       
      }

      // generate the list of stations from which the user can choose
      print drop_down_stations("station_list", "get_cars_and_jobs();");
      print '&nbsp;<input name="build_btn" value="Build" type="submit">';

    ?>
    <br /><br />
    <div id="instructions" style="visibility: hidden;">
    Select which job will pick up each of the cars and then click on the Build button. The cars wll be added to
    the selected job's pickup switch list for the this location.<br /><br />
    If the job column is left blank, the car will remain in place.<br /><br />
    The next destination in each car's route is displayed in <b>bold</b> text<br /><br />
    </div>
    <div id="car_table_div">
      <!-- the guts of the table are filled in by the HttpRequest call-back function -->
    </div>
    </form>
  </body>

    <script>
      // this javascript routine makes an HttpRequest that provides a list of cars at the selected
      // station and each car will have a drop-down list of jobs that could add it to their pickup switchlist

      function get_cars_and_jobs()
      {
        // check to see if the selection from the station list is non-blank
        if (document.getElementById('station_list').value.length > 0)
        {
          // submit the request for the cars at the selected station
          var xmlhttp = new XMLHttpRequest();
          xmlhttp.onreadystatechange = function()
          {
            if (this.readyState == 4 && this.status == 200)
            {
               populate_car_table(this);
            }
          }
          var url = 'get_cars_at_station.php?station=' + encodeURIComponent(document.getElementById('station_list').value);
          xmlhttp.open('GET', url, true);
          xmlhttp.send();
        }
      };

      // this is the call back function for the list of cars at the selected station
      function populate_car_table(xmlhttp)
      {
        if (xmlhttp.responseText == "None")
        {
          // tell the user that there aren't any cars at this location that are ready to move
          document.getElementById("car_table_div").innerHTML = "<tr>" + 
            "<td>There are no cars at this location that are ready to move</td></tr>";
        }
        else
        {
          // make the instruction block visible
          document.getElementById("instructions").style.visibility = "visible";

          // display the table being returned from the server
          document.getElementById("car_table_div").innerHTML = xmlhttp.responseText;
        }
      }

    </script>

</html>
