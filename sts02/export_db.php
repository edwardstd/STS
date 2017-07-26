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
    <h3>Export Database</h3>
    <form action="backup_db.php" method="get">
    Enter a name for the file to contain the exported database. This can be used to import at a later date<br />
    Then click the <b>Export</b> button.<br /><br />
    <input id="export_name" name="export_name" type="text">&nbsp;
    <input id="export_btn" name="export_btn" value="Export" type="submit" onclick="display_msg1();">
    <br /><br />
    <div id="msg1"></div>
    <?php
      // bring in the utility files
      require "open_db.php";

      // get a database connection
      $dbc = open_db();

      // has the Export button been clicked?
      if (isset($_GET["backup_btn"]))
      {
        // get the name of the export file from the form
        $export_name = "downloads/" . $_GET["export_name"];

        // check to see if this name has already been used
        if (file_exists($export_name))
        {
          print "<br /><br />That name has already been used. Try again.";
        }
        else
        {

        }
      }
    ?>
    </form>
    <script>
      function display_msg1()
      {
        document.getElementById("msg1").innerHTML = "Export operation started...";
      }
    </script>
</html>
