<?php

class Database
{
	private static $DB_FILE_NAME = "food.sqlite.db";
	private $dbConnection;
	
	public function open()
	{
		/* opens a database connection and returns true if successful */
		try
		{
			$this->dbConnection = new SQLite3(self::$DB_FILE_NAME);
			$rv = True;
		}
		catch(Exception $e)
		{
			$rv = False;
		}
		return $rv;
	}
	
	public function close()
	{
		/* closes the database connection */
		$this->dbConnection->close();
	}
	
	public function runAndReturn($query)
	{
		/* runs the specified query and returns the results */
		$results = $this->dbConnection->query($query);
		return $results;
	}
	
	public function runAndReturnArray($query)
	{
		/* runs the specified query and resturns the results as an associative array */
		
		//run query
		$results = $this->runAndReturn($query);
		
		//convert results into an associative array
		$resultsArray = array();
		while($row = $results->fetchArray())
		{
			$resultsArray[] = $row;
		}
		
		return $resultsArray;
	}
	
	public function run($query)
	{
		/* run the specified query */
		$this->dbConnection->exec($query);
	}
}
?>

