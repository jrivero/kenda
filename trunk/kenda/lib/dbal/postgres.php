<?php
/** 
*
* @package dbal
* @version $Id: postgres.php,v 1.26 2006/06/13 21:06:28 acydburn Exp $
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

	define('SQL_LAYER', 'postgres');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 7.3+
* @package dbal
*/
class dbal_postgres extends dbal
{
	var $last_query_text = '';
	
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->connect_string = '';

		if ($sqluser)
		{
			$this->connect_string .= "user=$sqluser ";
		}

		if ($sqlpassword)
		{
			$this->connect_string .= "password=$sqlpassword ";
		}

		if ($sqlserver)
		{
			if (strpos($sqlserver, ':') !== false)
			{
				list($sqlserver, $sqlport) = explode(':', $sqlserver);
				$this->connect_string .= "host=$sqlserver port=$sqlport ";
			}
			else
			{
				if ($sqlserver != "localhost")
				{
					$this->connect_string .= "host=$sqlserver ";
				}
			
				if ($port)
				{
					$this->connect_string .= "port=$port ";
				}
			}
		}

		if ($database)
		{
			$this->dbname = $database;
			$this->connect_string .= "dbname=$database";
		}

		$this->persistency = $persistency;

		$this->db_connect_id = ($this->persistency) ? @pg_pconnect($this->connect_string) : @pg_connect($this->connect_string);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
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
				return @pg_query($this->db_connect_id, 'BEGIN');
			break;

			case 'commit':
				return @pg_query($this->db_connect_id, 'COMMIT');
			break;

			case 'rollback':
				return @pg_query($this->db_connect_id, 'ROLLBACK');
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

			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if (!$this->query_result)
			{
				if (($this->query_result = @pg_query($this->db_connect_id, $query)) === false)
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
	* Build db-specific query data
	* @access: private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
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

			$query .= "\n LIMIT $total OFFSET $offset";

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

		return ($query_id) ? @pg_num_rows($query_id) : false;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->query_result) ? @pg_affected_rows($this->query_result) : false;
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

		return ($query_id) ? @pg_fetch_assoc($query_id, NULL) : false;
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
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			$row = $this->sql_fetchrow($query_id);
			return isset($row[$field]) ? $row[$field] : false;
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

		return ($query_id) ? @pg_result_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text != '')
		{
			if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text, $tablename))
			{
				$query = "SELECT currval('" . $tablename[1] . "_seq') AS last_value";
				$temp_q_id =  @pg_query($this->db_connect_id, $query);

				if (!$temp_q_id)
				{
					return false;
				}

				$temp_result = @pg_fetch_assoc($temp_q_id, NULL);
				@pg_free_result($query_id);

				return ($temp_result) ? $temp_result['last_value'] : false;
			}
		}

		return false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($this->open_queries[(int) $query_id]))
		{
			unset($this->open_queries[(int) $query_id]);
			return @pg_free_result($query_id);
		}

		return false;
	}

	/**
	* Escape string used in sql query
	* Note: Do not use for bytea values if we may use them at a later stage
	*/
	function sql_escape($msg)
	{
		return @pg_escape_string($msg);
	}

	/**
	* return sql error array
	* @access: private
	*/
	function _sql_error()
	{
		return array(
			'message'	=> (!$this->db_connect_id) ? @pg_last_error() : @pg_last_error($this->db_connect_id),
			'code'		=> ''
		);
	}

	/**
	* Close sql connection
	* @access: private
	*/
	function _sql_close()
	{
		return @pg_close($this->db_connect_id);
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

				$explain_query = $query;
				if (preg_match('/UPDATE ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}
				else if (preg_match('/DELETE FROM ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}

				if (preg_match('/^SELECT/', $explain_query))
				{
					$html_table = false;

					if ($result = @pg_query($this->db_connect_id, "EXPLAIN $explain_query"))
					{
						while ($row = @pg_fetch_assoc($result, NULL))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@pg_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @pg_query($this->db_connect_id, $query);
				while ($void = @pg_fetch_assoc($result, NULL))
				{
					// Take the time spent on parsing rows into account
				}
				@pg_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

}

} // if ... defined

?>