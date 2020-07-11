<?php
class db_operations {
  public function export_db($link, $db_name=''){
    // Get All Table Names From the Database
    $tables = array();
    $all_table = "";
    $sql = "SHOW TABLES";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
        $all_table .= $row[0].", ";
    }
    $sqlScript = "";
    $sqlScript .= "\nDROP TABLE ".(rtrim($all_table,", ")).";\n";
    foreach ($tables as $table) {
            // Prepare SQLscript for creating table structure
        $query = "SHOW CREATE TABLE $table";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_row($result);
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";
        $query = "SELECT * FROM $table";
        $result = mysqli_query($link, $query);
        $columnCount = mysqli_num_fields($result);
        // Prepare SQLscript for dumping data for each table
        for ($i = 0; $i < $columnCount; $i ++) {
            while ($row = mysqli_fetch_row($result)) {
                $sqlScript .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $columnCount; $j ++) {
                    $row[$j] = $row[$j];
                 if (isset($row[$j])) {
                        $sqlScript .= '"' . $row[$j] . '"';
                    } else {
                        $sqlScript .= '""';
                    }
                    if ($j < ($columnCount - 1)) {
                        $sqlScript .= ',';
                    }
                }
                $sqlScript .= ");\n";
            }
        }
        
        $sqlScript .= "\n"; 
    }

    if(!empty($sqlScript))
    {
        // Save the SQL script to a backup file
        $backup_file_name = $db_name . '_backup_' . date('d-m-Y') . '.sql';
        $fileHandler = fopen($backup_file_name, 'w+');
        $number_of_lines = fwrite($fileHandler, $sqlScript);
        fclose($fileHandler); 
     // Download the SQL backup file to the browser
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backup_file_name));
        ob_clean();
        flush();
        readfile($backup_file_name);
        exec('rm ' . $backup_file_name); 
    }
  }

  public function import_db($link, $import_file){
    $message = ''; 
    if($import_file != '')
    {
    $array = explode(".", $import_file);
    $extension = end($array);
    if($extension == 'sql')
    {
     $output = '';
     $count = 0;
     $file_data = file($import_file);
     foreach($file_data as $row)
     {
      $start_character = substr(trim($row), 0, 2);
      if($start_character != '--' || $start_character != '/*' || $start_character != '//' || $row != '')
      {
       $output = $output . $row;
       $end_character = substr(trim($row), -1, 1);
       if($end_character == ';')
       {
        if(!mysqli_query($link, $output))
        {
         $count++;
        }
        $output = '';
       }
      }
     }
     if($count > 0)
     {
      $message = 'There is an error in Database Import';
     }
     else
     {
      $message = '<label class="text-success">Database Successfully Imported';
     }
    }
    else
    {
     $message = 'Invalid File';
    }
    }
    else
    {
    $message = 'Please Select Sql File';
    }
    return $message;
  }
}
?>
