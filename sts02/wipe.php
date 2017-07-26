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
    <h3>Wipe Database</h3>
    Click the check boxes to activate the <b>Wipe</b> button<br />
    Click the <b>Wipe</b> button to erase the contents of the database and start over. There is no Undo feature.
    <br /><br />
    <form method="get" action="wipe.php">
      <input id="wipe_check_box1" value="reset" type="checkbox" onclick="toggle_wipe_check_box2();">
      Yes, I'm certain I want to wipe the entire database!<br /><br />
      <input id="wipe_check_box2" value="wipe2" type="checkbox" onclick="toggle_wipe_btn();" disabled>
      Yes, I'm REALLY REALLY certain that I want to wipe the entire database! (No going back!)<br /><br ?>
      <input id="wipe_btn" name="wipe_btn" value="WIPE" type="submit" disabled><br /><br />
    </form>

    <?php
      // this program deletes all data from all tables except settings, which it updates to default values

      // bring in the function files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // was the "Wipe" button clicked?
      if (isset($_GET["wipe_btn"]))
      {
        // drop the individual job tables
        $rs = mysqli_query($dbc, "select name from jobs");
        while ($row = mysqli_fetch_array($rs))
        {
          $sql = 'drop table `' . $row[0] . '`';
          if (!mysqli_query($dbc, $sql))
          {
            print "Drop Error: " . mysqli_error($dbc) . " SQL: " . $sql;
          }
          else
          {
            print "Deleting Job " . $row[0] . "...<br />";
          }
        }

        // delete records from the job table
        if(!mysqli_query($dbc, "delete from jobs"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All jobs removed...<br />";
        }

        // delete all records from the car orders table
        if(!mysqli_query($dbc, "delete from car_orders"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All car orders removed...<br />";
        }

        // delete all records from the car table
        if(!mysqli_query($dbc, "delete from cars"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All cars removed...<br />";
        }
        
        // delete all records from the shipment table
        if(!mysqli_query($dbc, "delete from shipments"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All shipments removed...<br />";
        }
        
        // delete all records from the location table
        if(!mysqli_query($dbc, "delete from locations"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All locations removed...<br />";
        }
        
        // delete all records from the empty_location table
        if(!mysqli_query($dbc, "delete from empty_locations"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All empty locations removed...<br />";
        }
        
        // delete all records from the car code table
        if(!mysqli_query($dbc, "delete from car_codes"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All car codes removed...<br />";
        }
        
        // delete all records from the routing table
        if(!mysqli_query($dbc, "delete from routing"))
        {
          print "Delete Error: " . mysqli_error($dbc) . " SQL: " . $sql;
        }
        else
        {
          print "All routing instructions removed...<br />";
        }
        
        // set the operating session to 0
        $sql = 'update settings set setting_value = "0" where setting_name = "session_nbr"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }

        // set the print width to 7.5 inches
        $sql = 'update settings set setting_value = "7.5in" where setting_name = "print_width"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }

        // set the railroad name to blanks
        $sql = 'update settings set setting_value = "" where setting_name = "railroad_name"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }

        // set the railroad initials to blanks
        $sql = 'update settings set setting_value = "" where setting_name = "railroad_initials"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Update Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }

        // tell the user what happened
        print "<br />Database wiped, default settings restored...";
      }
    ?>

    <script>
      function toggle_wipe_check_box2()
      {
        if (document.getElementById("wipe_check_box2").disabled)
          document.getElementById("wipe_check_box2").disabled = false;
        else
          {
            document.getElementById("wipe_check_box2").disabled = true;
            document.getElementById("wipe_check_box2").checked = false;
            document.getElementById("wipe_btn").disabled = true;
          }
      }
      function toggle_wipe_btn()
      {
        if (document.getElementById("wipe_btn").disabled)
          document.getElementById("wipe_btn").disabled = false;
        else
          document.getElementById("wipe_btn").disabled = true;
      }
    </script>
  </body>
</html>
