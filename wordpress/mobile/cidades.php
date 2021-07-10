<?php


/*


CREATE TABLE IF NOT EXISTS `pointlave_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `img` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `qnt` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `tasks`
--

INSERT INTO `pointlave_mobile` (`id`, `cidade`, `estado`, `img`, `link`, `qnt`, `status`) VALUES
(1, 'Juiz de Fora', 'Minas Gerais', 'https://www.pointlave.com.br/wp-content/uploads/juiz-de-fora.png', 'https://www.pointlave.com.br/juiz-de-fora/', 6, 1),
(2, 'Três Rios', 'Rio de Janeiro', 'https://www.pointlave.com.br/wp-content/uploads/tres-rios.png', 'https://www.pointlave.com.br/tres-rios/', 2, 0);

*/


   header('Content-type: application/json; charset=utf-32"');
   header('Access-Control-Allow-Origin: *');
   
   // Define database connection parameters
   $hn      = 'mysql';
   $un      = 'root';
   $pwd     = '@lava3135';
   $db      = 'pointlave_mobile';
   $cs      = 'utf8';

   // Set up the PDO parameters
   $dsn  = "mysql:host=" . $hn . ";port=3306;dbname=" . $db . ";charset=" . $cs;
   $opt  = array(
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                       );
   // Create a PDO instance (connect to the database)
   $pdo  = new PDO($dsn, $un, $pwd, $opt);
   $data = array();


   // Attempt to query database table and retrieve data
   try {
      $stmt    = $pdo->query('SELECT * FROM '.$db.' WHERE status = 1 ORDER BY estado ASC, cidade ASC');
      while($row  = $stmt->fetch(PDO::FETCH_OBJ))
      {
         // Assign each row of data to associative array
         $data[] = $row;
      }

      // Return data as JSON
      echo json_encode($data);
   }
   catch(PDOException $e)
   {
      echo $e->getMessage();
   }


?>