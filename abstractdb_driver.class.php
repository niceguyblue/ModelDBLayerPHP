<?php
/**
* AbstractDB Driver Base Class Definition
* 
* @package AbstractDB
* @author Pacific-Cybersoft
* @copyright (C) 2005 Pacific-Cybersoft. All Rights Reserved.
* @version v 1.0.2
* @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
*/

/**
* AbstractDB Driver included constant.
* 
* Flag that indicates that the driver base class file has been included.
* @access public
*/
define("ABSTRACTDB_DRIVER_INCLUDED", true);

/**
* AbstractDB Driver Base Class
* 
* Driver base class from which all other AbstractDB drivers extend.
* @package AbstractDB
* @abstract
*/
class AbstractDB_Driver 
{
	/* PRIVATE FIELDS */
	/**
	* List of connection and driver specific arguments.
	* @access private
	* @var array
	*/
	var $_arguments;
	/**
	* Database connection handle.
	* @access private
	* @var resource
	*/
	var $_connection;
	/**
	* The last error message.
	* @access private
	* @var string
	*/
	var $_error;

	/* PUBLIC PROPERTIES */
	/**
	* List of supported features of the currently loaded driver.
	* @access public
	* @var array
	*/
	var $Support;
	
	/**
	* AbstractDB Driver Constructor
	* 
	* Initilises an instance of the AbstractDB Driver base class.
	* @access public
	* @internal Stores the arguments parameter as a local field.
	* @param array $arguments A list of connection and driver specific arguments. 
	* See {@link AbstractDB} for details concerning connection arguments.
	*/
	function AbstractDB_Driver($arguments)
	{
		$this->ClearError();
		$this->_arguments = $arguments;
	}

	/* PUBLIC FUNCTIONS */
	/**
	* Gets the Number of Affected Rows
	* 
	* Gets the number of rows affected by the last query.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
	* @return int Returns the number of rows affected by the last executed query.
	*/
	function AffectedRows()
	{
		$this->ClearError();
		return 0;
	}
	
	/**
	* Closes the Database Connection
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported this method should return a default value of true.
	* @return bool Returns true if the database connection was successfully closed, otherwise false.
	*/
	function Close()
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Move a Result Pointer to the Specified Row
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
	* @param resource $rs A reference to a result handle returned by executing a query.
	* @param int $row_num The 0 based index of the row that the result pointer should move to.
	* @return bool Returns true if the operation was successful, otherwise false.
	*/
	function DataSeek(&$rs, $row_num)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Fetches a Result Row as an Associative Array
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return array Returns an associative array if the operation was successful, otherwise false.
	*/
	function FetchAssoc(&$rs)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Fetches the First Field Value
	* 
	* Fetches the value from the first field of a result row.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return mixed Returns the field value if the operation was successful, otherwise false.
	*/
	function FetchField(&$rs)
	{
		$this->ClearError();
		return false;
	}

	/**
	* Fetches a Result Row as an Object
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return object Returns an object if the operation was successful, otherwise false.
	*/
	function FetchObject(&$rs)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Fetches a Result Row
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return array Returns an array if the operation was successful, otherwise false.
	*/
	function FetchRow(&$rs)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Gets the Number of Fields
	* 
	* Gets the number of fields returned by given result handle.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return int Returns the number of fields returned by the last executed query.
	*/
	function FieldCount(&$rs) 
	{
		$this->ClearError();
		return 0;
	}
	
	/**
	* Gets the Field Names of a Query
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @param array $fields A reference to an array that will contain the field names.
	* @return bool Returns true if the operation was successful, otherwise false.
	*/
	function FieldNames(&$rs, &$fields)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Frees a Result Resource
	* 
	* Frees the resources associated with the given result handle.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported the method should return a default value of true.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return bool Returns true if the resource handle was successfully freed.
	*/
	function FreeResult(&$rs)
	{
		$this->ClearError();
		return true;
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
	* Gets the Last Inserted AUTO_INCREMENT ID
	* 
	* Gets the ID of the last AUTO_INCREMENT record inserted into the databse.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
	* @return mixed Returns either the ID of the last inserted AUTO_INCREMENT record, or -1 if the 
	* last query was not an insert.
	*/
	function InsertID()
	{
		$this->ClearError();
		return -1;
	}
	
	/**
	* Executes an SQL Statement.
	* 
	* Executes an SQL statement passed in as a parameter.
	* @access public
	* @internal This method must be overriden in extended classes or it will cause
	* the script to exit.
	* 
	* If the query fails an error should be set that explains as best as possible the 
	* reason for the query failure.
	* @param string $sql The SQL statement to execute on the database.
	* @return resource If the query was successful, the result handle of the query 
	* used in result fetching functions, otherwise false.
	*/
	function Query($sql)
	{
		die("Method AbstractDB_Driver::Query() must be redefined in extended classes without calling parent.");
	}
	
	/**
	* Executes an SQL Replace Query
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent if this feature is supported.
	* 
	* If this feature is not supported an error should be set explaining this.
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
	* @return resource If the replace query was successful, the result handle of the query, otherwise false.
	*/
	function Replace($table, $fields)
	{
		$this->ClearError();
		return false;
	}
	
	/**
	* Gets the Number of Rows
	* 
	* Gets the number of rows returned by given result handle.
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return int Returns the number of fields returned by the last executed query.
	*/
	function RowCount(&$rs)
	{
		$this->ClearError();
		return 0;
	}
	
	/**
	* Sets the Current Active Database
	* @access public
	* @internal This method must be overriden in extended classes with a call to parent.
	* 
	* If this operation fails an error should be set explaining as best as possible the reason for the failure.
	* @param string $dbName The name of the database to set active.
	* @return mixed The name of the previously active database, or false if an error occured.
	*/
	function SetDatabase($dbName)
	{
		$this->ClearError();
		return false;
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
	* Opens a Database Connection
	* 
	* Attempts to connect to the database using the parameters given in the constructor.
	* @access private
	* @internal This method must be overriden in extended classes or it will cause 
	* the script to exit.
	* 
	* The _connection field should be set to hold the resource link.
	* 
	* If the connection fails an error should be set that explains as best as possible the 
	* reason for the connection failure.
	* @return bool Returns true if the connection was successfully made, otherwise false.
	*/
	function Connect()
	{
		die("Method AbstractDB_Driver::Connect() must be redefined in extended classes without calling parent.");
	}

	/**
	* Sets the Error Message
	* @access private
	* @param string $scope The scope of the error, generally the function in which it occured.
	* @param string $message The actual error message.
	*/
	function SetError($scope, $message)
	{
		$this->_error = "$scope: $message";
	}
}
?>