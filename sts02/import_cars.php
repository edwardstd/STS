<html>
  <head>
    <title>Shipper-driven Traffic Simulator</title>
    <style>
      body {font: normal 20px Verdana, Arial, sans-serif;}
      table {border-collapse: collapse;}
      tr {vertical-align: top}
      th {border: 1px solid black; padding: 10px}
      th.vert_bottom {vertical-align: bottom}
      td {border: 1px solid black; padding: 10px}
      td.numbers {text-align: center}
    </style>
  </head>
  <body>
    <h1>Shipper-driven Traffic Simulator</h1>
    <a href="index.html">Return to Main Menu</a>
    <h2>Database Management</h2>
    <h3>Import Car Data from a File</h3>

    <?php
      // bring in the utility files
      require "open_db.php";

      // if the Import button was clicked, process the uploaded file
      if (isset($_POST['import']))
      {
        $target_dir = 'uploads/';
        $import_dir = getcwd() . '/uploads/';
        $target_file = $target_dir . basename($_FILES["import_file"]["name"]);
        $import_file = str_replace('\\','/',($import_dir . basename($_FILES["import_file"]["name"])));
        $file_type = pathinfo($target_file,PATHINFO_EXTENSION);

        // check if file already exists
        if (file_exists($target_file))
        {
          // delete the old file
          unlink($target_file);
        }

        // only allow files ending in ".csv"
        if($file_type == "csv")
        {
          if (move_uploaded_file($_FILES["import_file"]["tmp_name"], $target_file))
          {
            print basename( $_FILES['import_file']['name']). ' successfully uploaded.<br /><br />';

            // get a database connection
            $dbc = open_db();

            // check to see if this is an append or a replace operation
            if ($_POST['add_replace'] == "replace")
            {
              // remove the existing car data
              $sql = 'delete from cars';
              if (!mysqli_query($dbc, $sql))
              {
                print 'Delete error: ' . mysqli_error($dbc) . ' SQL: ' . $sql . '<br /><br />';
              }
              else
              {
                print 'Existing car data removed...<br /><br />';
              }
            }

            // import the file
            $sql = 'load data infile "' . $import_file . '"
                    into table cars
                    fields terminated by "," optionally enclosed by \'"\'
                    lines terminated by "\n"
                    ignore 1 lines';
            if (!mysqli_query($dbc, $sql))
            {
              print 'Load Data error: ' . mysqli_error($dbc) . ' SQL: ' . $sql . '<br /><br />';
            }
            else
            {
              print 'Car data successfully imported<br /><br />';
            }
          }
          else
          {
            print "Error uploading file, please try again";
          }
        }
        else
        {
          print "Incorrect file type. Only .csv file are allowed. Please try again.";
        }
      }
    ?>

    <form action="import_cars.php" method="post" enctype="multipart/form-data">
      Select the type of import (add or replace), the name of the  file to upload, and then click the "Import" button<br /><br />
      Be carefule when selecting the "Replace" option - There is no Undo!<br /><br />
      <input type="radio" name="add_replace" value="add" checked> Add to Car Data &nbsp;
      <input type="radio" name="add_replace" value="replace"> Replace all Car Data<br /><br />
      <input type="file" name="import_file" id="import_file" accept=".csv">
      <input type="submit" value="Import" name="import">
    </form>
  </body>
</html>
