<?php
/**
* @package AbstractDB
* @author Pacific-Cybersoft
* @copyright (C) 2005 Pacific-Cybersoft. All Rights Reserved.
* @version v 1.0.2
* @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
*/

/**
* AbstractDB Main Class
* 
* The main AbstractDB class used to interact with various DBMS packages via the
* use of driver classes.
* @package AbstractDB
* @access public
*/
class AbstractDB
{
	/* PRIVATE FIELDS */
	/**
	* List of connection arguments.
	* @access private
	* @var array
	*/
	var $_arguments;
	/**
	* Reference to the AbstractDB driver.
	* @access private
	* @var object
	*/
	var $_driver;
	/**
	* The last error message.
	* @access private
	* @var string
	*/
	var $_error;
	/**
	* Name of an error handling function passed in via {@link SetErrorHandler}.
	* @access private
	* @var string
	*/
	var $_error_handler;
	
	/* PUBLIC PROPERTIES */
	/**
	* List of supported features of the currently loaded driver.
	* @access public
	* @var array
	*/
	var $Support;
	
	/**
	* AbstractDB Constructor
	* 
	* Initilises an instance of the AbstractDB class.
	* @access public
	* @internal Parses connection arguments and loads the driver class.
	* @param array $arguments Database connection and driver specific arguments.
	* 
	* Arguments must be supplied as an associative array containing one of the following:
	* 
	* ConnectionString:
	* 
	* A string composed of the connection arguments and optional or driver specific options in the form
	* of <b>Type</b>://<b>Username</b>:<b>Password</b>@<b>Hostname</b>:<b>Port</b>/<b>Database</b>?<b>Options</b>/option1=value1&<b>Options</b>/option2=value2
	* 
	* e.g. $args = array("ConnectionString" => "mysql://root:pass@localhost/MyDatabase?Options/Persistent=1");
	* 
	* or
	* 
	* Type: The type of database to connect to. AbstractDB is currently only distributed with a MySQL driver.
	* 
	* Username: The database username.
	* 
	* Password: The database password.
	* 
	* Hostname: The database hostname or IP Address.
	* 
	* Database: The name of the database.
	* 
	* Options: An associative array of optional or driver specific options. See individual driver
	* documentation for a potential list of driver specific options. The value for Port should be 
	* specified in this list, otherwise the default port for the database type will be used.
	* 
	* e.g. $args = array("Type" => "mysql", "Username" => "root", "Password" => "pass", "Hostname" => "localhost", "Database" => "MyDatabase", "Options" => array("Persistent" => 1));
	* 
	* Type and Database are required parameters and must be specified.
	* @example instantiate_connectionstring.php Instantiation example using a ConnectionString argument.
	* @example instantiate_associative.php Instantiation example using an associative array of arguments.
	*/
	function AbstractDB($arguments)
	{
		$this->ClearError();
		if($this->ParseArguments($arguments))
			$this->LoadDriver();
	}
	
	/* PUBLIC FUNCTIONS */
	/**
	* Closes the Database Connection
	* @access public
	* @return bool Returns true if the database connection was successfully closed, otherwise false.
	*/
	function Close()
	{
		$result = $this->_driver->Close();
		$this->GetDriverError();
		return $result;
	}
	
	/**
	* Fetches the Next Row as an Associative Array
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $assoc A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_next_result.php FetchNextResult example.
	*/
	function FetchNextResultAssoc(&$rs, &$assoc)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchNextResultAssoc"))
		{
			if(isset($this->Support["FetchAssoc"]) && $this->Support["FetchAssoc"])
				$result = (($assoc = $this->_driver->FetchAssoc($rs)) != false);
			else
				$reuslt = false;
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches the Next Row as an Object
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param object $object A reference to an object that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_next_result.php FetchNextResult example.
	*/
	function FetchNextResultObject(&$rs, &$object)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchNextResultObject"))
		{
			if(isset($this->Support["FetchObject"]) && $this->Support["FetchObject"])
				$result = (($object = $this->_driver->FetchObject($rs)) != false);
			else
				$reuslt = false;
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches the Next Row
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $row A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_next_result.php FetchNextResult example.
	*/
	function FetchNextResultRow(&$rs, &$row)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchNextResultRow"))
		{
			$result = (($row = $this->_driver->FetchRow($rs)) != false);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row as an Associative Array
	* 
	* Fetches the first row as an associative array and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $assoc A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result.php FetchResult example.
	*/
	function FetchResultAssoc(&$rs, &$assoc)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultAssoc"))
		{
			$result = $this->FetchNextResultAssoc($rs, $assoc);
			$this->GetDriverError();
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches All Rows as Associative Arrays
	* 
	* Fetches all rows as associative arrays and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result_all.php FetchResultAll example.
	*/
	function FetchResultAssocAll(&$rs, &$all)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultAssocAll"))
		{
			while($result = $this->FetchNextResultAssoc($rs, $all[])){}
			array_pop($all);
			$this->GetDriverError();
			$result = (strlen($this->_error) == 0);
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Result Column
	* 
	* Fetches all rows of the first column and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $column A reference to an array that will contain the result column rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result_column.php FetchResultColumn example.
	*/
	function FetchResultColumn(&$rs, &$column)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultColumn"))
		{
			while($column[] = $this->_driver->FetchField($rs)){}
			array_pop($column);
			$this->GetDriverError();
			$result = (strlen($this->_error) == 0);
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Result Field
	* 
	* Fetches the value in the first column of the first row and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param mixed $field A reference to a variable that will contain the field value.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result_field.php FetchResultField example.
	*/
	function FetchResultField(&$rs, &$field)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultField"))
		{
			$result = (($field = $this->_driver->FetchField($rs)) != false);
			$this->GetDriverError();
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row as an Object
	* 
	* Fetches the first row as an object and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param object $object A reference to an object that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result.php FetchResult example.
	*/
	function FetchResultObject(&$rs, &$object)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultObject"))
		{
			$result = $this->FetchNextResultObject($rs, $object);
			$this->GetDriverError();
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches All Rows as Objects
	* 
	* Fetches all rows as objects and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result_all.php FetchResultAll example.
	*/
	function FetchResultObjectAll(&$rs, &$all)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultObjectAll"))
		{
			while($result = $this->FetchNextResultObject($rs, $all[])){}
			array_pop($all);
			$this->GetDriverError();
			$result = (strlen($this->_error) == 0);
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row
	* 
	* Fetches the first row and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $row A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result.php FetchResult example.
	*/
	function FetchResultRow(&$rs, &$row)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultRow"))
		{
			$result = $this->FetchNextResultRow($rs, $row);
			$this->GetDriverError();
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches All Rows
	* 
	* Fetches all rows and then frees the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_result_all.php FetchResultAll example.
	*/
	function FetchResultRowAll(&$rs, &$all)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchResultRowAll"))
		{
			while($result = $this->FetchNextResultRow($rs, $all[])){}
			array_pop($all);
			$this->GetDriverError();
			$result = (strlen($this->_error) == 0);
			$this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row as an Associative Array
	* 
	* Fetches the row at the specified position in the result set as an associative array.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param int $row_num The position in the result set of the row to return.
	* @param array $assoc A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_seek_result.php FetchSeekResult example.
	*/
	function FetchSeekResultAssoc(&$rs, $row_num, &$assoc)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchSeekResultAssoc"))
		{
			if(isset($this->Support["DataSeek"]) && $this->Support["DataSeek"])
			{
				if($result = $this->_driver->DataSeek($rs, $row_num))
					$result = $this->FetchNextResultAssoc($rs, $assoc);
			}
			else
				$reuslt = false;
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row as an Object
	* 
	* Fetches the row at the specified position in the result set as an object.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param int $row_num The position in the result set of the row to return.
	* @param object $object A reference to an object that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_seek_result.php FetchSeekResult example.
	*/
	function FetchSeekResultObject(&$rs, $row_num, &$object)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchSeekResultObject"))
		{
			if(isset($this->Support["DataSeek"]) && $this->Support["DataSeek"])
			{
				if($result = $this->_driver->DataSeek($rs, $row_num))
					$result = $this->FetchNextResultObject($rs, $object);
			}
			else
				$reuslt = false;
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Fetches a Row
	* 
	* Fetches the row at the specified position in the result set.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param int $row_num The position in the result set of the row to return.
	* @param array $row A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example fetch_seek_result.php FetchSeekResult example.
	*/
	function FetchSeekResultRow(&$rs, $row_num, &$row)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "FetchSeekResultRow"))
		{
			if(isset($this->Support["DataSeek"]) && $this->Support["DataSeek"])
			{
				if($result = $this->_driver->DataSeek($rs, $row_num))
					$result = $this->FetchNextResultRow($rs, $row);
			}
			else
				$reuslt = false;
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Frees a Result Resource
	* 
	* Frees the resources associated with the given result handle returned by executing a query.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return bool Returns true if the resource handle was successfully freed.
	*/	
	function FreeResult(&$rs)
	{
		if($result = $this->IsResource($rs, "FreeResult"))
		{
			$result = $this->_driver->FreeResult($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Gets the Number of Affected Rows
	* @access public
	* @return int Returns the number of rows affected by the last executed query, or false if the 
	* driver does not support this feature.
	* @example affected_rows.php GetAffectedRows example.
	*/
	function GetAffectedRows()
	{
		$this->ClearError();
		if(isset($this->Support["AffectedRows"]) && $this->Support["AffectedRows"])
			$result = $this->_driver->AffectedRows();
		else
			$result = false;
		$this->GetDriverError();
		return $result;
	}

	/**
	* Gets the Name of the Current Database
	* @access public
	* @return string The name of the current database.
	*/	
	function GetDatabase()
	{
		return $this->_arguments["Database"];
	}
	
	/**
	* Gets the Number of Fields
	* 
	* Gets the number of fields returned by the given result handle.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return int Returns the number of fields returned by the last executed query.
	* @example field_count.php GetFieldCount example.
	*/
	function GetFieldCount(&$rs)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "GetFieldCount"))
		{
			$result = $this->_driver->FieldCount($rs);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Gets the Field Names of a Query
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $fields A reference to an array that will contain the field names.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example field_names.php GetFieldNames example.
	*/
	function GetFieldNames(&$rs, &$fields)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "GetFieldNames"))
		{
			$result = $this->_driver->FieldNames($rs, $fields);
			$this->GetDriverError();
		}
		return $result;
	}
	
	/**
	* Gets the Last Insert ID
	* @access public
	* @return mixed Returns either the ID of the last inserted AUTO_INCREMENT record, or -1 if the 
	* last query was not an insert, or false if the driver does not support this feature.
	* @example insert_id.php GetInsertID example.
	*/
	function GetInsertID()
	{
		$this->ClearError();
		if(isset($this->Support["InsertID"]) && $this->Support["InsertID"])
			$result = $this->_driver->InsertID();
		else
			$result = false;
		$this->GetDriverError();
		return $result;
	}
	
	/**
	* Gets the Last Error.
	* @access public
	* @return string The last error message.
	*/
	function GetLastError()
	{
		return $this->_error;
	}
	
	/**
	* Gets the Number of Rows
	* 
	* Gets the number of rows returned by the last query of the given result handle.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return int Returns the number of rows returned by the last executed query.
	* @example row_count.php GetRowCount example.
	*/
	function GetRowCount(&$rs)
	{
		$this->ClearError();
		if($result = $this->IsResource($rs, "GetRowCount"))
		{
			$result = $this->_driver->RowCount($rs);
			$this->GetDriverError();
		}
		return $result;
	}

	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement.
	* @access public
	* @internal If the query did not execute successfully an error is set explaining as 
	* best as possible the reason for the failure.
	* @param string $sql The SQL statement to be executed.
	* @return resource The result handle for use in fetch result functions.
	* @example query.php Query example.
	*/
	function Query($sql)
	{
		$this->ClearError();
		$result = &$this->_driver->Query($sql);
		$this->GetDriverError();
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves the first result row as an associative array, then
	* frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param array $assoc A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result.php Query example.
	*/
	function QueryAssoc($sql, &$assoc)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultAssoc($rs, $assoc);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves all result rows as associative arrays, then
	* frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result_all.php Query All example.
	*/
	function QueryAssocAll($sql, &$all)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultAssocAll($rs, $all);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves all rows of the first result column, then
	* frees the result set.
	* @access public
	* @param string $sql The SQL statement to be executed.
	* @param array $column A reference to an array that will contain the result column rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_column.php QueryColumn example.
	*/
	function QueryColumn($sql, &$column)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultColumn($rs, $column);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves the value in the first column of the first 
	* row, then frees the result set.
	* @access public
	* @param string $sql The SQL statement to be executed.
	* @param mixed $field A reference to a variable that will contain the field value.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_field.php QueryField example.
	*/
	function QueryField($sql, &$field)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultField($rs, $field);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves the first result row as an object, then
	* frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param object $object A reference to an object that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result.php Query example.
	*/
	function QueryObject($sql, &$object)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultObject($rs, $object);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves all result rows as objects, then
	* frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result_all.php Query All example.
	*/
	function QueryObjectAll($sql, &$all)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultObjectAll($rs, $all);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves the first result row, then
	* frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param array $row A reference to an array that will contain the result row.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result.php Query example.
	*/
	function QueryRow($sql, &$row)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultRow($rs, $row);
		return $result;
	}
	
	/**
	* Executes an SQL Statement
	* 
	* Executes the given SQL statement and retrieves all result rows, then frees the result set.
	* @param string $sql The SQL statement to be executed.
	* @param array $all A reference to an array that will contain the result rows.
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example query_result_all.php Query All example.
	*/
	function QueryRowAll($sql, &$all)
	{
		$this->ClearError();
		$result = false;
		if($rs = &$this->Query($sql))
			$result = $this->FetchResultRowAll($rs, $all);
		return $result;
	}
	
	/**
	* Executes an SQL Replace Query
	* @access public
	* @param string $table The name of the table to execute the replace query on.
	* @param array $fields An associative array of field definitions. Keys should be the field names and
	* values should be an associative array containing the following keys:
	* 
	* Key => bool indicating that this field is the primary key or part of a unique index. Key values must not be NULL.
	* 
	* Type => either "text", "numeric", "bool".
	* 
	* Value => the value of the field.
	* 
	* Null => bool indicating if the value of the field should be set to NULL.
	* 
	* e.g. $fields = array("Field1" => array("Key" => true, "Type" => "numeric", "Value" => 123, "Null" => false));
	* @return bool Returns true if the operation was successful, otherwise false.
	* @example replace.php Replace example.
	*/
	function Replace($table, $fields) 
	{
		$this->ClearError();
		$result = $this->_driver->Replace($table, $fields) ? true : false;
		$this->GetDriverError();
		return $result;
	}
	
	/**
	* Sets the Current Database
	* @access public
	* @param string $dbName The name of the new database to perform queries on.
	* @return string The old database name that was set before calling this method, or false if an error occurred.
	*/
	function SetDatabase($dbName)
	{
		$this->ClearError();
		$result = $this->_driver->SetDatabase($dbName);
		if($result !== false && $result != $this->_arguments["Database"])
			$this->_arguments["Database"] = $result;
		$this->GetDriverError();
		return $result;
	}
	
	/**
	* Sets an Error Handling Function.
	* @access public
	* @param string $functionName The name of a function to be called when an error occurs.
	* @return bool Returns true if the error handling function was successfully set, otherwise false.
	* @example set_error_handler.php SetErrorHandler example.
	*/
	function SetErrorHandler($functionName)
	{
		if(function_exists($functionName))
		{
			$this->_error_handler = $functionName;
			return true;
		}
		else
		{
			$this->SetError("SetErrorHandler", "Could not set error handler, function '$functionName' does not exist.");
			return false;
		}
	}
	
	/* PRIVATE FUNCTIONS */
	/**
	* Clears the latest error message.
	* @access private
	*/
	function ClearError()
	{
		$this->_error = "";
	}
	
	/**
	* Ensures Required Connection Arguments Exist
	* @access private
	* @internal If there are missing arguments an error is set stating which 
	* arguments are missing.
	* @return bool Returns true if all required connection arguments have been set, otherwise false.
	*/
	function EnsureRequiredArguments()
	{
		$result = true;
		$error = "";
		if(isset($this->_arguments))
		{
			if(!isset($this->_arguments["Type"]))
			{
				$result = false;
				$error .= ((strlen($error) > 0) ? ", " : "") . "Type";
			}
			/*if(!isset($this->_arguments["Hostname"]))
			{
				$result = false;
				$error .= ((strlen($error) > 0) ? ", " : "") . "Hostname";
			}
			if(!isset($this->_arguments["Username"]))
			{
				$result = false;
				$error .= ((strlen($error) > 0) ? ", " : "") . "Username";
			}
			if(!isset($this->_arguments["Password"]))
			{
				$result = false;
				$error .= ((strlen($error) > 0) ? ", " : "") . "Password";
			}*/
			if(!isset($this->_arguments["Database"]))
			{
				$result = false;
				$error .= ((strlen($error) > 0) ? ", " : "") . "Database";
			}
		}
		else
			$result = false;
		if(!$result)
			$this->SetError("EnsureRequiredArguments", "The following required arguments have not been set: " . $error);
		return $result;
	}
	
	/**
	* Gets the Latest Error from the Driver
	* @access private
	* @internal Sets an error based on the driver error.
	*/
	function GetDriverError()
	{
		$driverError = $this->_driver->GetLastError();
		if(strlen($driverError) > 0)
		{
			$error = explode(": ", $driverError);
			switch(count($error))
			{
				case 1:
					$this->SetError("{$this->_arguments["Type"]}Driver", $error[0]);
					break;
				case 2:
					$this->SetError($error[0], $error[1]);
					break;
			}
		}
	}
	
	/**
	* Checks That a Parameter is a Resource
	* 
	* Checks that a given parameter is a resource type variable.
	* @access private
	* @param resource $resource A variable to ensure is a resource.
	* @param string $callee The name of the method calling this function. Used in the setting of the error message.	
	* @return bool Returns true if the given parameter is a resource, otherwise false.
	*/
	function IsResource($resource, $callee)
	{
		if(!($result = is_resource($resource)))
			$this->SetError($callee, "Resource specified is not a valid resource.");
		return $result;
	}
	
	/**
	* Loads an AbstractDB Database Driver
	* 
	* Loads the AbstractDB driver for the type of database being connected to.
	* @access private
	* @internal If the driver could not be loaded an error is set explaining the 
	* reason for the failure.
	* @return bool Returns true if the driver was successfully loaded, otherwise false.
	*/
	function LoadDriver()
	{
		$dirname = dirname(__FILE__);
		if(!defined("ABSTRACTDB_DRIVER_INCLUDED"))
		{
			$driverfile = "$dirname/abstractdb_driver.class.php";
			if(!file_exists($driverfile))
			{
				$this->SetError("LoadDriver", "Could not load the AbstractDB Driver base class. File $driverfile not found.");
				return false;
			}
			include($driverfile);
		}
	    if(!defined("ABSTRACTDB_" . strtoupper($this->_arguments["Type"]) . "_INCLUDED"))
	    {
		    $driverfile = "$dirname/drivers/abstractdb_" . strtolower($this->_arguments["Type"]) . ".php";
		    if(!file_exists($driverfile))
		    {
		        $this->SetError("LoadDriver", "Driver file $driverfile could not be found.");
		        return false;
		    }
	    	include($driverfile);
	    }
	    $driver_class = "AbstractDB_" . $this->_arguments["Type"];
	    $this->_driver = new $driver_class($this->_arguments);
		$this->GetDriverError();
	    if(strlen($this->_error) > 0)
	        return false;
		else
	    	$this->Support = $this->_driver->Support;
	    return true;
	}
	
	/**
	* Parses Connection and Driver Specific Arguments
	* @access private
	* @param array $arguments A list of connection and driver specific arguments.
	* @return bool Returns true if arguments were successfully parsed, otherwise false.
	*/
	function ParseArguments($arguments)
	{
		$result = false;
	    if(isset($arguments["ConnectionString"]))
			$this->ParseConnectionArguments($arguments["ConnectionString"]);
		else
		{
			if(isset($arguments["Type"]))
				$this->_arguments["Type"] = $arguments["Type"];
			if(isset($arguments["Username"]))
				$this->_arguments["Username"] = $arguments["Username"];
			if(isset($arguments["Password"]))
				$this->_arguments["Password"] = $arguments["Password"];
			if(isset($arguments["Hostname"]))
				$this->_arguments["Hostname"] = $arguments["Hostname"];
			if(isset($arguments["Options"]))
			{
				if(isset($arguments["Options"]["Port"]))
					$this->_arguments["Options"]["Port"] = $arguments["Options"]["Port"];
				if(isset($arguments["Options"]["Persistent"]))
					$this->_arguments["Options"]["Persistent"] = $arguments["Options"]["Persistent"];
			}
			if(isset($arguments["Database"]))
				$this->_arguments["Database"] = $arguments["Database"];
		}
		return $this->EnsureRequiredArguments();
	}
	
	/**
	* Parses ConnectionString Argument
	* 
	* Parses the ConnectionString argument passed in via the constructor.
	* @access private
	* @param string $connectionString A connection string in the format
	* <b>Type</b>://<b>User</b>:<b>Pass</b>@<b>Host</b>:<b>Port</b>/<b>Database</b>?<b>Options</b>/option1=value1&<b>Options</b>/option2=value2
	* @return bool Returns true if the connection string was successfully parsed, otherwise false.
	* @todo There may be a use for the fragment part of the parsed connection string.
	*/
	function ParseConnectionArguments($connectionString)
	{
		$result = true;
		$parsed = parse_url($connectionString);
		if(isset($parsed["scheme"]))
			$this->_arguments["Type"] = urldecode($parsed["scheme"]);
		else
			$result = false;
		if(isset($parsed["host"]))
			$this->_arguments["Hostname"] = urldecode($parsed["host"]);
		else
			$result = false;
		if(isset($parsed["port"]))
			$this->_arguments["Options"]["Port"] = urldecode($parsed["port"]);
		if(isset($parsed["user"]))
			$this->_arguments["Username"] = urldecode($parsed["user"]);
		else
			$result = false;
		if(isset($parsed["pass"]))
			$this->_arguments["Password"] = urldecode($parsed["pass"]);
		else
			$result = false;
		if(isset($parsed["path"]))
			$this->_arguments["Database"] = substr(urldecode($parsed["path"]), 1);
		else
			$result = false;
		if(isset($parsed["query"]))
		{
			$options=explode("&",$parsed["query"]);
			for($option=0; $option < count($options); $option++)
			{
				if(gettype($equal = strpos($options[$option], "=")) != "integer")
				{
					// This option does not have a value.
					continue;
				}
				$argument = urldecode(substr($options[$option], 0, $equal));
				$value = urldecode(substr($options[$option], $equal + 1));
				if(gettype($slash = strpos($argument, "/")) == "integer")
				{
					if(substr($argument, 0, $slash) != "Options")
					{
						// Not a valid Options argument
						continue;
					}
					$this->_arguments["Options"][substr($argument, $slash + 1)] = $value;
				}
				else
					$this->_arguments[$argument] = $value;
			}
		}
		if(isset($parsed["fragment"])){}
	    return $result;
	}
	
	/**
	* Sets the Latest Error Message.
	* 
	* Sets the latest error message and calls an error handling function if one was set.
	* @access private
	* @param string $scope The scope of the error, generally the function in which it occured.
	* @param string $message The actual error message.
	*/
	function SetError($scope, $message)
	{
		$this->_error = "$scope: $message";
		if(strcmp($function = $this->_error_handler, ""))
		{
			$error = array("Scope" => $scope, "Message" => $message);
			$function($this, $error);
		}
	}
	
	
}
?>
