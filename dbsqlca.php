<?php

/*if (!ioncube_file_is_encoded())
	die("License Error 1sybs");
if (ioncube_loader_iversion()!=710818 && ioncube_loader_iversion()!=40010)
	die("License Error 2sybs");
if (!ioncube_license_matches_server())
	die("License Error 3sybs");
if (ioncube_license_has_expired())
	die("License Error 4sybs");
$_LP = ioncube_license_properties();
if (!isset($_LP['opensoftsybs']))
	die("License Error 5sybs");*/

class pgsqlDB
{

  var $connection = 0;
  var $error;
  // An associative array of cursors for our database connection.
  // Note that the cursor named "_default" is reserved as the 
  // default cursor.
  //
  var $cursors=array();

  // The name of our currently selected cursor
  var $cursor='_default';
  

  function __construct($datasource='localhost',$dbuser, $dbpass, $database){

    $cadena= 'host='.$datasource.' user='.$dbuser.' password='.$dbpass.' port=5432 dbname='.$database;
    // Try to log in
    if (!($this->connection=@pg_connect($cadena)) ) {
      $this->error='Could not connect to PGSQL database';
      return('pgsqlDB->Connection: '.$this->error);
    }
    // Try to select a default database if it exists
    // Assume success
    return(null);
  }

  function db_close() {
    if (!@pg_close($this->connection) ) {
      $this->error='Could not close database connection '
        .$this->get_error();
      return ($this->error);
    }
    // Assume success
    return(null);
  } // End of function db_close()

  function query($query, $cursorname='_default') {
    // Get our current cursor
	//print_r($query);
    $cursor=&$this->cursors[$cursorname];
    if (!($cursor=pg_exec($this->connection, $query))) {
      $this->error="Could not query database ".$this->get_error();
      return(-1);
    }
    // Assume success
    return($this->numrows());
  } // End of query()

	function query_params($query,$params,$cursorname='_default') {
		$cursor = &$this->cursors[$cursorname];
		if (!($cursor = pg_query_params($this->connection,$query,$params))) {
			$this->error = "Could not query database " . $this->get_error();
			return -1;
		}

		return $this->numrows();
	}

  function numrows($cursorname='_default'){
    return @pg_numrows($this->cursors[$cursorname]);
  }
  
  function numrows_affected($cursorname='_default'){
    return @pg_affected_rows($this->cursors[$cursorname]);
  }
  
  function fetchRow($cursorname='_default') {
    // Init
    $results = array();
    // Our cursor
    $cursor = &$this->cursors[$cursorname];
    if (!($results = @pg_fetch_array($cursor))) {
      if ($cursor == "") {
        $error = "The cursor '".$this->cursor
          ."' appears to be empty!";
        $this->error = "Db_driver_pgsql->fetchRow(): ".$error;
        return(null);
      }
      // Otherwise, just return the error
      //return($this->get_error());
      return(null);
    }
    // Assume success
    return($results);
  } // End of fetch()

  function fetchAll($cursorname='_default') {
    // Init
    $results = array();
    // Our cursor
    $cursor = &$this->cursors[$cursorname];
    if (!($results = @pg_fetch_all($cursor))) {
      if ($cursor == "") {
        $error = "The cursor '".$this->cursor
          ."' appears to be empty!";
        $this->error = "Db_driver_pgsql->fetchAll(): ".$error;
        return(null);
      }
      // Otherwise, just return the error
      //return($this->get_error());
      return(null);
    }
    // Assume success
    return($results);
  } // End of fetch()

  function numfields($cursorname='_default'){
    return @pg_numfields($this->cursors[$cursorname]);
  }

  function perform($table, $data, $action = 'insert', $parameters = '', $ObtSentencia = false){
    $action = strtoupper($action);
    reset($data);
    if ($action == 'INSERT') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';}
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
        case 'now()':
          $query .= 'now(), ';
        break;
        case 'null':
          $query .= 'null, ';
        break;
        case 'current_timestamp':
          $query .= 'current_timestamp, ';
        break;
        case 'current_date':
          $query .= 'current_date, ';
        break;
        case 'current_time':
          $query .= 'current_time, ';
        break;
        default:
          //$value = html_chars_replace_encode($value);
	    if(eregi("to_date", $value))
	    {
	       $query .= "" . $value . ", ";
	    }else{
	       $query .= "'" . addslashes($value) . "', ";
	    }
          //$query .= "'" . addslashes($value) . "', ";
          break;
        }
      }
      $query = substr($query, 0, -2) . ')';
      
  //    file_put_contents($query."\n\n", "/queryinsert", FILE_APPEND);
    } elseif ($action == 'UPDATE') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
        case 'now()':
          $query .= $columns . ' = now(), ';
        break;
        case 'null':
          $query .= $columns .= ' = null, ';
        break;
        case 'current_timestamp':
          $query .= $columns .= ' = current_timestamp, ';
        break;
        case 'current_date':
          $query .= $columns .= ' = current_date, ';
        break;
        case 'current_time':
          $query .= $columns .= ' = current_time, ';
        break;
        default:
          //$value = html_chars_replace_encode($value);
	    if(eregi("to_date", $value))
	    {
	       $query .= $columns .= ' = '.$value.', ';
	    }else{
	       $query .= $columns . " = '" . addslashes($value) . "', ";
	    }
          break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
      //echo "QUERY : $query\n";
    }elseif ($action == 'DELETE') {
      $query = 'delete from ' . $table;
      while (list($columns, $value) = each($data)) {
      switch ((string)$value) {
      case 'now()':
        $query .= $columns . ' = now(), ';
        break;
      case 'null':
        $query .= $columns .= ' = null, ';
        break;
      default:
        //$value = html_chars_replace_encode($value);
        $query .= $columns . " = '" . addslashes($value) . "', ";
        break;
      }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }
     //echo "QUERY : $query \n";
     if($ObtSentencia == true)
     {
        $Result['query'] = $query;
        $Result['exec']  = $this->query($query);
        return $Result;
     }
     print_r($query);
     return $this->query($query);
  }//end perform

  function get_error() {
    $error=@pg_errormessage($this->connection);
    if ($error)
      return('PGSQL Error : '.$error);
    // Otherwise, return null
    return(null);
  } // End of function get_error()

  function functionDB($funcionDB="")
  {
    $registro = $this->query("select $funcionDB");
    if ($this->numrows() > 0)
    {
      $row = $this->fetchRow();
      return $row[0];
    }
    else return "";
  }

  function firstRow($query)
  {
    $registro = $this->query($query);
    if ($this->numrows() > 0)
      return $this->fetchRow();
    else return array();
  }


/*
 *
 * pg extend block table
 */

  function pg_query($sql) {
    return pg_query($this->connection, $sql);
  }

  function cancel_query() {
    pg_cancel_query($this->connection);
  }

  function _close() {
    pg_close($this->connection);
  }


  function send_query($sql) {
    return pg_send_query($this->connection, $sql);
  }

  function send_query_gr($sql) {
    $return = array();
    $query = pg_send_query($this->connection, $sql);
    $res = pg_get_result($query);
    if ($res === false) {
      $return['find'] = false;
    } else {
      $return['find'] = true;
    }
    return $return;
  }

  function connection_busy() {
    return pg_connection_busy($this->connection);
  }

  function get_result() {
    pg_get_result($this->connection);
  }

  function free_result($sql) {
    pg_free_result($sql);
  }

  function _exec($sql) {
    return pg_exec($this->connection, $sql);
  }
}

//util functions for postgresql 

function pg_array_to_array($pgarray){
  $array = array();
  $str = substr($pgarray, 1, -1); //remove {}
  $temArr = explode(',',$pgarray);
  foreach($temArr as $elto) {
    $isarray = strpos($elto,'{'); //subarray?
    if($isarray === FALSE) {
      $array[] = trim(str_replace('"', '', $elto));
    } else {
      $array[] = pg_array_to_array($elto);
    }
  }
}

?>
