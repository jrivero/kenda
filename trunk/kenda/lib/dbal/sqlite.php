<?php
/** 
*
* @package dbal
* @version $Id: sqlite.php,v 1.21 2006/06/17 11:32:06 davidmj Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @ignore
*/
if (!defined('SQL_LAYER'))
{

	define('SQL_LAYER', 'sqlite');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* Sqlite Database Abstraction Layer
* @package dbal
*/
class dbal_sqlite extends dbal
{
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$error = '';
		$this->db_connect_id = ($this->persistency) ? @sqlite_popen($this->server, 0666, $error) : @sqlite_open($this->server, 0666, $error);

		if ($this->db_connect_id)
		{
			@sqlite_query('PRAGMA short_column_names = 1', $this->db_connect_id);
		}
		
		return ($this->db_connect_id) ? true : array('message' => $error);
	}

	/**
	* SQL Transaction
	* @access: private
	*/
	function _sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				return @sqlite_query('BEGIN', $this->db_connect_id);
			break;

			case 'commit':
				return @sqlite_query('COMMIT', $this->db_connect_id);
			break;

			case 'rollback':
				return @sqlite_query('ROLLBACK', $this->db_connect_id);
			break;
		}

		return true;
	}

	/**
	* Base query method
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if (!$this->query_result)
			{
				if (($this->query_result = @sqlite_query($query, $this->db_connect_id)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
					$cache->sql_save($query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0 && $this->query_result)
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
				}
			}
			else if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('fromcache', $query);
			}
		}
		else
		{
			return false;
		}

		return ($this->query_result) ? $this->query_result : false;
	}

	/**
	* Build LIMIT query
	*/
	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0) 
	{
		if ($query != '')
		{
			$this->query_result = false; 

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0)
			{
				$total = -1;
			}

			$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

			return $this->sql_query($query, $cache_ttl); 
		}
		else
		{
			return false;
		}
	}

	/**
	* Return number of rows
	* Not used within core code
	*/
	function sql_numrows($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @sqlite_num_rows($query_id) : false;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @sqlite_changes($this->db_connect_id) : false;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		return ($query_id) ? @sqlite_fetch_array($query_id, SQLITE_ASSOC) : false;
	}

	/**
	* Fetch field
	* if rownum is false, the current row is used, else it is pointing to the row (zero-based)
	*/
	function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum === false)
			{
				return @sqlite_column($query_id, $field);
			}
			else
			{
				$this->sql_rowseek($rownum, $query_id);
				return @sqlite_column($query_id, $field);
			}
		}

		return false;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @sqlite_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		return ($this->db_connect_id) ? @sqlite_last_insert_rowid($this->db_connect_id) : false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		return true;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return @sqlite_escape_string($msg);
	}

	/**
	* return sql error array
	* @access: private
	*/
	function _sql_error()
	{
		return array(
			'message'	=> @sqlite_error_string(@sqlite_last_error($this->db_connect_id)),
			'code'		=> @sqlite_last_error($this->db_connect_id)
		);
	}

	/**
	* Build db-specific query data
	* @access: private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* Close sql connection
	* @access: private
	*/
	function _sql_close()
	{
		return @sqlite_close($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @access: private
	*/
	function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'start':
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlite_query($query, $this->db_connect_id);
				while ($void = @sqlite_fetch_array($result, SQLITE_ASSOC))
				{
					// Take the time spent on parsing rows into account
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

}

} // if ... define

?>