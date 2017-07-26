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
    <h3>Restore Database</h3>
    <form action="restore_db.php" method="get">
    Click on the radio button for the backup copy that you want to restore.<br />
    Then click the <b>Restore</b> button.<br /><br />
    The current contents of the database will be erased and replaced with the data stored in the backup copy.<br /><br />
    <?php
      // bring in the utility files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // get the list of name from the backup table
      $sql = 'select name from backups';
      $rs = mysqli_query($dbc, $sql);
      if (mysqli_num_rows($rs) > 0)
      {
        // since there are backup copies, we can now display the restore button
        print '<input id="restore_btn" name="restore_btn" value="Restore" type="submit" onclick="display_msg1();"><br /><br />';

        // build a table of backup names with radio buttons
        print "<table>";
        while ($row = mysqli_fetch_array($rs))
        {
          print '<tr>
                   <td><input name="restore_name" value="' . $row[0] . '" type="radio"></td>
                   <td>' . $row[0] . '</td>
                 </tr>';
        }
        print "</table>";
      }
      else
      {
        print "No backup copies available.";
      }
    ?>
    <br /><br />
    <div id="msg1"></div>
    <?php
      // has the Restore button been clicked?
      if (isset($_GET["restore_btn"]))
      {
        // get the name of the backkup to be restored from the form
        $restore_name = $_GET["restore_name"];

        // first, drop the individual job tables
        print '<br /><b>Removing existing data...</b><br /><br />';

        $rs = mysqli_query($dbc, "select name from jobs");
        while ($row = mysqli_fetch_array($rs))
        {
          $sql = 'drop table `' . $row[0] . '`';
          if (!mysqli_query($dbc, $sql))
          {
            print "Drop Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Removing " . $row[0] . "...<br />";
          }
        }
        if (!mysqli_query($dbc, "drop table jobs"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "All jobs removed...<br />";
        }

        // drop the rest of the tables

        if (!mysqli_query($dbc, "drop table car_orders"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Car Orders removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table cars"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Cars removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table shipments"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Shipments removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table locations"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Locations removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table empty_locations"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Empty locations removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table routing"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Routing removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table car_codes"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Car Codes removed...<br />";
        }

        if (!mysqli_query($dbc, "drop table settings"))
        {
          print "Drop Table Error: " . mysqli_error($dbc);
        }
        else
        {
          print "Settings removed...<br />";
        }

        /* --------------------------------------------/
        /----------- restore the backup copy ----------/
        /-------------------------------------------- */
        print '<br /><b>Restoring backup data...</b><br /><br />';

        // settings
        $sql = 'create table settings as select * from `' . $restore_name . '`.settings';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table settings add primary key(setting_name)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Settings restored...<br />";
          }
        }

        // car codes
        $sql = 'create table car_codes as select * from `' . $restore_name . '`.car_codes';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table car_codes add primary key(code)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Car Codes restored...<br />";
          }
        }

        // locations
        $sql = 'create table routing as select * from `' . $restore_name . '`.routing';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table routing add primary key(station)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Routing restored...<br />";
          }
        }

        // locations
        $sql = 'create table locations as select * from `' . $restore_name . '`.locations';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table locations add primary key(code)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Locations restored...<br />";
          }
        }

        // empty locations
        $sql = 'create table empty_locations as select * from `' . $restore_name . '`.empty_locations';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table empty_locations add primary key(shipment, priority, location)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Empty Locations restored...<br />";
          }
        }

        // shipments
        $sql = 'create table shipments as select * from `' . $restore_name . '`.shipments';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table shipments add primary key(code)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Shipments...<br />";
          }
        }

        // cars
        $sql = 'create table cars as select * from `' . $restore_name . '`.cars';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table cars add primary key(reporting_marks)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Cars restored...<br />";
          }
        }

        // car orders
        $sql = 'create table car_orders as select * from `' . $restore_name . '`.car_orders';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table car_orders add primary key(waybill_number)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Car Orders restored...<br />";
          }
        }

        //jobs
        $sql = 'create table jobs as select * from `' . $restore_name . '`.jobs';
        if (!mysqli_query($dbc, $sql))
        {
          print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        else
        {
          $sql = 'alter table jobs add primary key(name)';
          if (!mysqli_query($dbc, $sql))
          {
            print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            print "Jobs restored...<br />";
          }
        }

        // get a list of the jobs in the job table and restore them as well
        $rs = mysqli_query($dbc, "select name from jobs");
        while ($row = mysqli_fetch_array($rs))
        {
          $sql = 'create table `' . $row[0] . '` as select * from `' . $restore_name . '`.`' . $row[0] . '`';
          if (!mysqli_query($dbc, $sql))
          {
            print "Create Table Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            $sql = 'alter table `' . $row[0] . '` add primary key(step_number)';
            if (!mysqli_query($dbc, $sql))
            {
              print "Add Primary Key Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
            }
            else
            {
              print "Job " . $row[0] . " restored...<br />";
            }
          }
        }

        print "<br /><b>" . $restore_name . "</b> restored.";
      }
    ?>
    </form>
    <script>
      function display_msg1()
      {
        document.getElementById("msg1").innerHTML = "Restore operation started...";
      }
    </script>
</html>
