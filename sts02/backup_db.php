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
    <h3>Back Up Database</h3>
    <form action="backup_db.php" method="get">
    Enter a name for the backup copy. This can be used to restore the information at a later date<br />
    Then click the <b>Back Up</b> button.<br /><br />
    <input id="backup_name" name="backup_name" type="text">&nbsp;
    <input id="backup_btn" name="backup_btn" value="Back Up" type="submit" onclick="display_msg1();">
    <br /><br />
    <div id="msg1"></div>
    <?php
      // bring in the utility files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // has the Back Up button been clicked?
      if (isset($_GET["backup_btn"]))
      {
        // get the name of the backup from the form
        $backup_name = $_GET["backup_name"];

        // check to see if this name has already been used
        $sql = 'select name from backups where name = "' . $backup_name . '"';
        $rs = mysqli_query($dbc, $sql);
        if (mysqli_num_rows($rs) > 0)
        {
          print "<br /><br />That name has already been used. Try again.";
        }
        else
        {
          // create a backup database using the name provided by the user and copy each table from the
          // sts database into the backup database, also store the name of the backup in the sts backup table

          $sql = 'create database `' . $backup_name . '`';
          if (!mysqli_query($dbc, $sql))
          {
            print "Create Database Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
          }
          else
          {
            // now that the backup database has been created, copy all of the tables (except backup) to it
            
            // get a list of tables
            $table_count = 0;
            $sql = "show tables";
            $rs = mysqli_query($dbc, $sql);
            while ($row = mysqli_fetch_array($rs))
            {
              $table_name[$table_count] = $row[0];
              $table_count++;
            }

            // copy the tables (except the one containing the backup names)
            for ($i=0; $i<$table_count; $i++)
            {
              if ($table_name[$i] != "backups")
              {
                $sql = 'create table `' . $backup_name . '`.`' . $table_name[$i] . '` as select * from `' . $table_name[$i] . '`';
                if (!mysqli_query($dbc, $sql))
                {
                  print "Select Into Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
                }
                else
                {
                  print ucwords(str_replace("_", " ", $table_name[$i])) . " copied...<br />";
                }
              }
            }

            // store this backup's name in the backup table
            $sql = 'insert into backups values ("' . $backup_name . '")';
            if (!mysqli_query($dbc, $sql))
            {
              print "Insert Error: " . mysqli_error($dbc) . " SQL: " . $sql . "<br />";
            }
            else
            {
              print "<br />Back up <b>" . $backup_name . "</b> complete...";
            }
          }
        }
      }
    ?>
    </form>
    <script>
      function display_msg1()
      {
        document.getElementById("msg1").innerHTML = "Back up operation started...";
      }
    </script>
</html>
