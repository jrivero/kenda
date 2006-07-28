<?php
/** 
*
* @package dbal
* @version $Id: mssql.php,v 1.24 2006/06/13 21:06:28 acydburn Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
if (!defined('IN_APP'))
{
	exit;
}

/**
* @ignore
*/
if (!defined('SQL_LAYER'))
{

 define('SQL_LAYER', 'mssql');
 //define('DEBUG_EXTRA', true);
 include('dbal.php');

/**
* MSSQL Database Abstraction Layer
* Minimum Requirement is MSSQL 2000+
* @package dbal
*/
class dbal_mssql extends dbal
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

		$this->db_connect_id = ($this->persistency) ? @mssql_pconnect($this->server, $this->user, $sqlpassword) : @mssql_connect($this->server, $this->user, $sqlpassword);

		if ($this->db_connect_id && $this->dbname != '')
		{
			if (!@mssql_select_db($this->dbname, $this->db_connect_id))
			{
				@mssql_close($this->db_connect_id);
				return false;
			}
		}

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
				return @mssql_query('BEGIN TRANSACTION', $this->db_connect_id);
			break;

			case 'commit':
				return @mssql_query('commit', $this->db_connect_id);
			break;

			case 'rollback':
				return @mssql_query('ROLLBACK', $this->db_connect_id);
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
			
			//echo $query . '<br />';
			
			if( strpos($query,' LIMIT') )
			{
			  preg_match('# (LIMIT ([0-9]+)[, ]*([0-9]+)*)?$#s', $query, $limits);
			  $query = preg_replace('#(LIMIT ([0-9]+)[, ]*([0-9]+)*)?$#s', '', $query);
        $row_offset = ( $limits[2] ) ? $limits[2] : 0;
				$num_rows = ( $limits[3] ) ? $limits[3] : $limits[2];

        return $this->sql_query_limit($query,$num_rows,$row_offset,$cache_ttl);
      }

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if (!$this->query_result)
			{
				if (($this->query_result = @mssql_query($query, $this->db_connect_id)) === false)
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

			$row_offset = ($total) ? $offset : '';
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
				$this->sql_rowseek($result, $row_offset);
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

		return ($query_id) ? @mssql_num_rows($query_id) : false;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mssql_rows_affected($this->db_connect_id) : false;
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

		$row = @mssql_fetch_assoc($query_id);

		// I hope i am able to remove this later... hopefully only a PHP or MSSQL bug
		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ') ? '' : $value;
			}
		}

		return $row;
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

		return ($query_id) ? @mssql_data_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$result_id = @mssql_query('SELECT @@IDENTITY', $this->db_connect_id);
		if ($result_id)
		{
			if ($row = @mssql_fetch_assoc($result_id))
			{
				@mssql_free_result($result_id);
				return $row['computed'];
			}
			@mssql_free_result($result_id);
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

		if (isset($this->open_queries[$query_id]))
		{
			unset($this->open_queries[$query_id]);
			return @mssql_free_result($query_id);
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
	* return sql error array
	* @access: private
	*/
	function _sql_error()
	{
		$error = array(
			'message'	=> @mssql_get_last_message($this->db_connect_id),
			'code'		=> ''
		);

		// Get error code number
		$result_id = @mssql_query('SELECT @@ERROR as code', $this->db_connect_id);
		if ($result_id)
		{
			$row = @mssql_fetch_assoc($result_id);
			$error['code'] = $row['code'];
			@mssql_free_result($result_id);
		}

		// Get full error message if possible
		$sql = 'SELECT CAST(description as varchar(255)) as message 
			FROM master.dbo.sysmessages
			WHERE error = ' . $error['code'];
		$result_id = @mssql_query($sql);
		
		if ($result_id)
		{
			$row = @mssql_fetch_assoc($result_id);
			if (!empty($row['message']))
			{
				$error['message'] .= '<br />' . $row['message'];
			}
			@mssql_free_result($result_id);
		}

		return $error;
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
		return @mssql_close($this->db_connect_id);
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
					@mssql_query("SET SHOWPLAN_TEXT ON;", $this->db_connect_id);
					if ($result = @mssql_query($explain_query, $this->db_connect_id))
					{
						@mssql_next_result($result);
						while ($row = @mssql_fetch_row($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@mssql_query("SET SHOWPLAN_TEXT OFF;", $this->db_connect_id);
					@mssql_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mssql_query($query, $this->db_connect_id);
				while ($void = @mssql_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mssql_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

}

} // if ... define

?>