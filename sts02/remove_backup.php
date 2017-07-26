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
    <h3>Remove Backup Database</h3>
    <form action="remove_backup.php" method="get">
    Click on the radio button for the backup copy that you want to remove.<br />
    Then click the <b>Remove</b> button.<br /><br />
    WARNING! After you have clicked on the <b>Remove</b> button there is no<br />
    way to recover the removed backup database!<br /><br />
    <?php
      // bring in the utility files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // has the Remove button been clicked?
      $msg2 = "";
      if (isset($_GET["remove_btn"]))
      {
        // get the name of the backkup to be removed from the form
        $remove_name = $_GET["remove_name"];

        // drop the specified database
        $sql = 'drop database `' . $remove_name . '`';
        if (!mysqli_query($dbc, $sql))
        {
          print "Drop Error: " . $mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }

        // remove it's name from the backups table
        $sql = 'delete from backups where name = "' . $remove_name . '"';
        if (!mysqli_query($dbc, $sql))
        {
          print "Delete Error: " . $mysqli_error($dbc) . " SQL: " . $sql . "<br />";
        }
        $msg2 = "<br /><b>" . $remove_name . "</b> removed.";
      }

      // get the list of name from the backup table
      $sql = 'select name from backups';
      $rs = mysqli_query($dbc, $sql);
      if (mysqli_num_rows($rs) > 0)
      {
        // since there are backup copies, we can now display the remove button
        print '<input id="remove_btn" name="remove_btn" value="Remove" type="submit" onclick="display_msg1();"><br /><br />';

        // build a table of backup names with radio buttons
        print "<table>";
        while ($row = mysqli_fetch_array($rs))
        {
          print '<tr>
                   <td><input name="remove_name" value="' . $row[0] . '" type="radio"></td>
                   <td>' . $row[0] . '</td>
                 </tr>';
        }
        print "</table>";
      }
      else
      {
        print "No backup copies available.";
      }
      print $msg2;
    ?>
    <br />
    <div id="msg1"></div>
    </form>
    <script>
      function display_msg1()
      {
        document.getElementById("msg1").innerHTML = "Remove operation started...";
      }
    </script>
</html>
