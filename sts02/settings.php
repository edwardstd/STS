<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      td {border: 1px solid black; padding: 10px}
    </style>
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Database Management</h2>
    <h3>Simulation Settings</h3>
    <form>
      <div id="instructions">
        After modifying setting values, click the Update button to put the new values into effect.
      </div>
      <br />
      <input name="update_btn" value="Update" type="submit"><br /><br />

      <?php
        // display a list of settings and update the settings values if the user clicks the Update button
      
        // bring in the utility files
        require "open_db.php";

        // get a database connection
        $dbc = open_db();

        // was the Update button clicked?
        if (isset($_GET["update_btn"]))
        {
          // collect the names of all of the settings
          $row_count = 0;
          $sql = 'select setting_name from settings';
          $rs = mysqli_query($dbc, $sql);
          while ($row = mysqli_fetch_array($rs))
          {
            $settings[$row_count] = $row[0];
            $row_count++;
          }

          // now go through the array of setting names and update any changes
          for ($i=0; $i<$row_count; $i++)
          {
            if (strlen($_GET[$settings[$i]]) > 0)
            {
              // only make a change if there's a new value
              $setting_value = $_GET[$settings[$i]];
              $sql = 'update settings set setting_value = "' . $setting_value . '" where setting_name = "' . $settings[$i] . '"';
              if (!mysqli_query($dbc, $sql))
              {
                print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql;
              }
            }
          }
          
        }

        // build an sql query to bring in the settings and their values
        $sql = 'select * from settings';
        $rs = mysqli_query($dbc, $sql);

        if (mysqli_num_rows($rs) > 0)
        {
          print '<table>';
          print '<tr><th>Setting</th><th>Current Value</th><th>New Value</th></tr>';

          while ($row = mysqli_fetch_array($rs))
          {
            print '<tr>
                     <td>' . $row[1] . '</td>
                     <td>' . $row[2] . '</td>
                     <td><input name="' . $row[0] . '" type="text"></td>
                   </tr>';
          }
                
          print '</table>';
        }
        else
        {
          print "No settings found. Curious... There should be some here. :-(";
        }
      ?>
    </form>
  </body>
</html>
