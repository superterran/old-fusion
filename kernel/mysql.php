<?php

class mysql
{
		
	var $server;
	var $database;
	var $username;
	var $password;
	var $port;
	var $prefix;
	
		
	function dbConnect($config)
	{
	
		$this->server = $config->server;
		$this->database = $config->database;
		$this->username = $config->username;
		$this->password = $config->password;
		$this->prefix = $config->prefix;
		$this->port = $config->port;
		
		$this->mysql = mysql_connect($this->server, $this->username, $this->password) or die (mysql_error());
		mysql_select_db($this->database, $this->mysql) or die (mysql_error());		
		
		return true;
	}
	
	function query($sql) 
	{
		$sql = str_replace('~~', $this->prefix, $sql);

		$query = mysql_query($sql, $this->mysql) or die (mysql_error().'<br>'."\n".$sql);
		return $query;
		
	}
	
}	