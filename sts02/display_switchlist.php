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
    <h2>Reports</h2>
    <h3>Switchlist</h3>
    <!-- this form generates another page that is modified for better printing  -->
    <!-- the user needs to use the browser's back button to return to this page -->
    <!-- or user the link displayed at the bottom of the last page              -->
    <form action="printable_switchlist.php" method="get"> 
    Select Job and then click the Display button.<br />
    A new page will be displayed that is formatted for printing.<br />
    Use the browser's "Back" button to return to this page<br />
    or click on the link that is displayed at the bottom of the last page.<br /><br />

    <?php
      // bring in the utility files
      require"drop_down_list_functions.php";
      require "open_db.php";

      // generate a drop-down list of jobs and the submit button
      print drop_down_jobs("job_name", "");
      print '<input name="display_btn" value="Display" type="submit"><br /><br >';
    ?>
    </form>
  </body>
</html>
