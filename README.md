# php-mysql-bckp-restore

### This class will help you in backup and restore mysql db using php.

> Usage:
```php
<?php
  include_once 'mysql_backup_restore.php';

  if(isset($_POST['export'])){
    $obj_db_op->export_db($link, $db_name);
  }

  if(isset($_POST['import'])){
    $obj_db_op->import_db($link, $_FILES["sql_file"]["name"]);
  }
?>
```
