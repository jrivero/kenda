<?php
/** 
*
* @package dbal
* @version $Id: mssql_odbc.php,v 1.14 2006/06/13 21:06:28 acydburn Exp $
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

	define('SQL_LAYER', 'mssql_odbc');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* Unified ODBC functions
* Unified ODBC functions support any database having ODBC driver, for example Adabas D, IBM DB2, iODBC, Solid, Sybase SQL Anywhere...
* Here we only support MSSQL Server 2000+ because of the provided schema
* @package dbal
*/
class dbal_mssql_odbc extends dbal
{
	var $last_query_text = '';

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @odbc_pconnect($this->server, $this->user, $sqlpassword) : @odbc_connect($this->server, $this->user, $sqlpassword);

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
				return @odbc_autocommit($this->db_connect_id, false);
			break;

			case 'commit':
				$result = @odbc_commit($this->db_connect_id);
				@odbc_autocommit($this->db_connect_id, true);
				return $result;
			break;

			case 'rollback':
				$result = @odbc_rollback($this->db_connect_id);
				@odbc_autocommit($this->db_connect_id, true);
				return $result;
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
				if (($this->query_result = @odbc_exec($this->db_connect_id, $query)) === false)
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

			$row_offset = ($total) ? $offset : 0;
			$num_rows = ($total) ? $total : $offset;

			if (strpos($query, 'SELECT DISTINCT') === 0)
			{
				$query = 'SELECT DISTINCT TOP ' . ($row_offset + $num_rows) . ' ' . substr($query, 15);
			}
			else
			{
				$query = 'SELECT TOP ' . ($row_offset + $num_rows) . ' ' . substr($query, 6);
			}

			$result = $this->sql_query($query, $cache_ttl);

			// Seek by $row_offset rows
			if ($row_offset)
			{
				for ($i = 0; $i < $row_offset; $i++)
				{
					$this->sql_fetchrow($result);
				}
			}

			return $result;
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

		return ($query_id) ? @odbc_num_rows($query_id) : false;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @odbc_num_rows($this->query_result) : false;
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

		return ($query_id) ? @odbc_fetch_array($query_id) : false;
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

		$this->sql_freeresult($query_id);
		$query_id = $this->sql_query($this->last_query_text);

		if (!$query_id)
		{
			return false;
		}

		// We do not fetch the row for rownum == 0 because then the next resultset would be the second row
		for ($i = 0; $i < $rownum; $i++)
		{
			if (!$this->sql_fetchrow($query_id))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$result_id = @odbc_exec($this->db_connect_id, 'SELECT @@IDENTITY');

		if ($result_id)
		{
			if (@odbc_fetch_array($result_id))
			{
				$id = @odbc_result($result_id, 1);	
				@odbc_free_result($result_id);
				return $id;
			}
			@odbc_free_result($result_id);
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
			return @odbc_free_result($query_id);
		}

		return false;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return str_replace("'", "''", $msg);
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
	* return sql error array
	* @access: private
	*/
	function _sql_error()
	{
		return array(
			'message'	=> @odbc_errormsg(),
			'code'		=> @odbc_error()
		);
	}

	/**
	* Close sql connection
	* @access: private
	*/
	function _sql_close()
	{
		return @odbc_close($this->db_connect_id);
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
					@odbc_exec($this->db_connect_id, "SET SHOWPLAN_TEXT ON;");
					if ($result = @odbc_exec($this->db_connect_id, $explain_query))
					{
						@odbc_next_result($result);
						while ($row = @odbc_fetch_array($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@odbc_exec($this->db_connect_id, "SET SHOWPLAN_TEXT OFF;");
					@odbc_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @odbc_exec($this->db_connect_id, $query);
				while ($void = @odbc_fetch_array($result))
				{
					// Take the time spent on parsing rows into account
				}
				@odbc_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

}

} // if ... define

?>