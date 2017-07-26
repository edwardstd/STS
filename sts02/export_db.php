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
    <form action="export_db.php" method="get">
    Enter a name for the file to contain the exported database. This file can be downloaded, stored off-site, and then be used to restore the entire MySQL database at a later date.<br /><br />
    Click the <b>Export</b> button to start the operation.<br /><br />
    <input id="export_name" name="export_name" type="text">&nbsp;
    <input id="export_btn" name="export_btn" value="Export" type="submit">
    <br /><br />
    <?php
      // has the Export button been clicked?
      if (isset($_GET["export_btn"]))
      {
        // get the name of the export file from the form
        $export_name = "uploads/" . $_GET["export_name"];

        // check to see if this name has already been used
        if (file_exists($export_name))
        {
          print "<br /><br />That name has already been used. Try again.";
        }
        else
        {
          if (stristr(strtoupper(PHP_OS), "WIN"))
          {
            // we are running on Windows so use the relative path to the xampp copy of mysqldump
            $dump_cmd = "..\..\mysql\bin\mysqldump --user=root --add-drop-database --all-databases > " . $export_name;
          }
          else
          {
            // we are not running on Windows so just assume that mysqldump is in the PATH
            $dump_cmd = "mysqldump --user=root --password=sts02isOK --add-drop-database --all-databases > " . $export_name;
          }
          // print "<br /><br />" . $dump_cmd . "<br /><br />";
          print "Export operation started...<br /><br />";
          flush();
          exec($dump_cmd);
          print 'Export complete, click <a href="' . $export_name . '">here</a> to download the file if desired.';
        }
      }
    ?>
    </form>
</html>
