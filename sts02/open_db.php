<?php
  // standard routine to open a connection to the sts database
  // the connection to the database is stored in $dbc

  function open_db()
  {
    // bring in the credentials
    require "credentials.php";

    // open the connection
    $dbc = mysqli_connect($server_name, $user_name, $password, $db_name);

    // check to see if the connection worked
    if (!$dbc)
    {
      die("Connection to sts_user database failed: " . mysqli_connect_error());
    }

    // return the database connection
    return $dbc;
  }
?>
