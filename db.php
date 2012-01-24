<?php 

class db  {
  private $connection_string = "<dsn here>";
  private $connection;

  function __construct()  {

    $connection= pg_connect($this->connection_string);
    if (!$connection)  {
      echo "Unable to connect to the database server \n". pg_last_error($connection);
      exit();
    }

    $this->connection = $connection;
  }

  function db_query($query)  {
    $connection = pg_connect($this->connection_string);
    $result = pg_query($connection, $query);
    if ($result === false)  {
      echo "Unable to execute query $query \n".pg_last_error() ;
      return false;
    }
     
    else return $result;
  }

}
?>