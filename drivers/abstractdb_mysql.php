<?php
/**
* AbstractDB MySQL Driver
* 
* @package AbstractDB
* @author Pacific-Cybersoft
* @copyright (C) 2005 Pacific-Cybersoft. All Rights Reserved.
* @version v 1.0.2
* @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
*/

/**
* MySQL Driver included constant.
* 
* Flag that indicates that the MySQL driver has been included.
* @access public
*/
define("ABSTRACTDB_MYSQL_INCLUDED", true);

/**
* AbstractDB MySQL Driver Class
* @access public
* @package AbstractDB
*/
class AbstractDB_MySQL extends AbstractDB_Driver
{
	/**
	* AbstractDB MySQL Driver Constructor
	* 
	* Initilises an instance of the AbstractDB MySQL Driver class.
	* @internal Calls the {@link AbstractDB_Driver} constructor passing the given parameters and sets
	* the values of the Support list.
	*/
	function AbstractDB_MySQL($arguments)
	{
	    $this->AbstractDB_Driver($arguments);
		$this->Support["AffectedRows"] =
			$this->Support["InsertID"] = 
			$this->Support["FetchObject"] = 
			$this->Support["FetchAssoc"] = 
			$this->Support["DataSeek"] = true;
			
	}
	
	/* PUBLIC FUNCTIONS */
	function AffectedRows()
	{
		parent::AffectedRows();
		return @mysql_affected_rows($this->_connection);
	}
	
	function Close()
	{
		if($this->_connection)
		{
			$result = @mysql_close($this->_connection);
			if(!$result)
			{
				$error = mysql_error($this->_connection);
				if(strlen($error) == 0 && isset($php_errormsg))
					$error = $php_errormsg;
				else
					$error = "The connection could not be closed, reason unknown.";
				$this->SetError("Close", $error);
			}
			return $result;
		}
		else
		{
			$this->SetError("Close", "No connection to close.");
			return false;
		}
	}
	
	function DataSeek(&$rs, $row_num)
	{
		parent::DataSeek($rs, $row_num);
		return @mysql_data_seek($rs, $row_num);
	}
	
	function FetchAssoc(&$rs)
	{
		parent::FetchAssoc($rs);
		return @mysql_fetch_assoc($rs);
	}
	
	function FetchField(&$rs)
	{
		parent::FetchField($rs);
		$result = false;
		if($row = $this->FetchRow($rs))
			$result = $row[0];
		return $result;
	}
	
	function FetchObject(&$rs)
	{
		parent::FetchObject($rs);
		return @mysql_fetch_object($rs);
	}
	
	function FetchRow(&$rs)
	{
		parent::FetchRow($rs);
		return @mysql_fetch_row($rs);
	}
	
	function FieldCount(&$rs)
	{
		parent::FieldCount($rs);
		return @mysql_num_fields($rs);
	}
	
	function FieldNames(&$rs, &$fields)
	{
		parent::FieldNames($rs, $fields);
		for($index = 0; $index < $this->FieldCount($rs); $index++)
		{
			$fields[] = @mysql_field_name($rs, $index);
			if($error = mysql_error($this->_connection))
			{
				$this->SetError("FieldNames", $error);
				return false;
			}
		}
		return true;
	}
	
	function FreeResult(&$rs)
	{
		$result = @mysql_free_result($rs);
		if(!$result)
		{
			$error = mysql_error($this->_connection);
			if(strlen($error) == 0 && isset($php_errormsg))
				$error = $php_errormsg;
			else
				$error = "Could not free resource handle, reason unknown.";
			$this->SetError("FreeResult", $error);
		}
		return $result;
	}
	
	function InsertID()
	{
		parent::InsertID();
		$result = @mysql_insert_id($this->_connection);
		if($result == 0)
			return -1;
		else
			return $result;
	}
	
	function Query($sql)
	{
		if(!$this->Connect())
			return false;
		if(!(@mysql_select_db($this->_arguments["Database"], $this->_connection) && $rs = @mysql_query($sql, $this->_connection)))
		{
			$this->SetError("Query", @mysql_error($this->_connection));
			return false;
		}
		return $rs;
	}
	
	function Replace($table, $fields)
	{
		parent::Replace($table, $fields);
		for($keys = 0, $query = $values = "", reset($fields), $field = 0; $field < count($fields); next($fields), $field++)
		{
			$fieldname = key($fields);
			if($field > 0)
			{
				$query .= ", ";
				$values .= ", ";
			}
			$query .= $fieldname;
			if(isset($fields[$fieldname]["Null"]) && $fields[$fieldname]["Null"])
				$value = "NULL";
			else
			{
				if(!isset($fields[$fieldname]["Value"]))
				{
					$this->SetError("Replace", "Value was not set for field $fieldname.");
					return false;
				}
				switch(isset($fields[$fieldname]["Type"]) ? $fields[$fieldname]["Type"] : "text")
				{
					case "text":
						$value = "'" . str_replace("'", "\'", $fields[$fieldname]["Value"]) . "'";
						break;
					case "boolean":
						$value = ($fields[$fieldname]["Value"]) ? "1" : "0";
						break;
					case "numeric":
						$value = strval($fields[$fieldname]["Value"]);
						break;
					default:
						$this->SetError("Replace", "Type was not specified for field $fieldname.");
						return false;
				}
			}
			$values .= $value;
			if(isset($fields[$fieldname]["Key"]) && $fields[$fieldname]["Key"])
			{
				if($value == "NULL")
				{
					$this->SetError("Replace", "Key values can not be NULL.");
					return false;
				}
				$keys++;
			}
		}
		if($keys == 0)
		{
			$this->SetError("Replace", "No Key fields were specified.");
			return false;
		}
		return $this->Query("REPLACE INTO $table ($query) VALUES($values)");
	}
	
	function RowCount(&$rs)
	{
		parent::RowCount($rs);
		return @mysql_num_rows($rs);
	}
	
	function SetDatabase($dbName)
	{
		if(!$this->Connect())
			return false;
		if(!($result = @mysql_select_db($dbName, $this->_connection)))
			$this->SetError("SetDatabase", mysql_error($this->_connection));
		else
		{
			$result = $this->_arguments["Database"];
			$this->_arguments["Database"] = $dbName;
		}
		return $result;
	}
	
	/* PRIVATE FUNCTIONS */
	function Connect()
	{
		if($this->_connection)
			return true;
	    if(isset($this->_arguments["Options"]["Persistent"]) && 
			$this->_arguments["Options"]["Persistent"] === true &&
			function_exists("mysql_pconnect"))
	        $connect = "mysql_pconnect";
		else
		    $connect = "mysql_connect";
		$port = isset($this->_arguments["Options"]["Port"]) ? ":" . $this->_arguments["Options"]["Port"] : "";
		
		$args = array();
		if(isset($this->_arguments["Hostname"]))
		{
			$server = $this->_arguments["Hostname"] . $port;
			$args[] = $server;
			if(isset($this->_arguments["Username"]))
			{
				$args[] = $this->_arguments["Username"];
				if(isset($this->_arguments["Password"]))
					$args[] = $this->_arguments["Password"];
			}
		}
		switch(count($args))
		{
			case 1:
				$this->_connection = @$connect($args[0]);
				break;
			case 2:
				$this->_connection = @$connect($args[0], $args[1]);
				break;
			case 3:
				$this->_connection = @$connect($args[0], $args[1], $args[2]);
				break;
		}
		if(!$this->_connection)
		{
		    $this->SetError("Connect", isset($php_errormsg) ? $php_errormsg : "Could not connect to MySQL server");
		    return false;
		}
		return true;
	}
}
?>
