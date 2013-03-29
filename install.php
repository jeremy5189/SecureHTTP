<!DOCTYPE html>
<html>
  <head>
    <title>Install SecureHTTP</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  </head>
  <body>
    <center>
      <h2>This file will install SecureHTTP MySQL Table</h2>
      <h2>Before you install, please edit "config.example.php" and rename it as "config.php"</h2>
      <h2><a href="install.php?opr=install">Click Me to Install</a></h2> 
    <?php
    
    if( isset($_GET['opr']) && $_GET['opr'] == 'install' )
    {
        include_once('config.php');
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME); 
        mysql_query("SET NAMES UTF8;");
        
        $create_table = "CREATE TABLE IF NOT EXISTS `$TABLE_NAME` (
                          `CLIENT_ID` varchar(8) NOT NULL,
                          `CLIENT_PUBLIC_KEY` text NOT NULL,
                          `SERVER_PRIVATE_KEY` text NOT NULL,
                          `SERVER_PUBLIC_KEY` text NOT NULL,
                          `ACTIVE` tinyint(1) NOT NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        $result = mysql_query($create_table, $link);
        mysql_close($link);
        if($result)
            echo "<h2>Install Success!</h2>";
        else
            echo "<h2>Please check your MySQL Settings</h2>";
    }
    
    ?>
    </center>
  </body>
</html>