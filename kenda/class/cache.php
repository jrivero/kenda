<?php
/**
*
* @package acm
* @version $Id: acm_file.php,v 1.32 2006/06/13 21:06:27 acydburn Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* ACM File Based Caching
* @package acm
*/
class cache
{
	var $vars = array();
	var $var_expires = array();
	var $is_modified = false;

	var $sql_rowset = array();

	/**
	* Set cache path
	*/
	function cache()
	{
		$this->cache_dir = VAR_PATH . 'cache/';
	}

	/**
	* Load global cache
	*/
	function load()
	{

		if (file_exists($this->cache_dir . 'data_global.php'))
		{
			include($this->cache_dir . 'data_global.php');
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	* Unload cache object
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
	}

	/**
	* Save modified objects
	*/
	function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		$file = '<?php $this->vars=' . $this->format_array($this->vars) . ";\n\$this->var_expires=" . $this->format_array($this->var_expires) . ' ?'.'>';

		if ($fp = @fopen($this->cache_dir . 'data_global.php', 'wb'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, $file);
			@flock($fp, LOCK_UN);
			fclose($fp);
		}

		$this->is_modified = false;
	}

	/**
	* Tidy cache
	*/
	function tidy()
	{

		$dir = opendir($this->cache_dir);
		while (($entry = readdir($dir)) !== false)
		{
			if (!preg_match('/^(sql_|data_(?!global))/', $entry))
			{
				continue;
			}

			$expired = true;
			include($this->cache_dir . $entry);
			if ($expired)
			{
				@unlink($this->cache_dir . $entry);
			}
		}
		@closedir($dir);

		if (file_exists($this->cache_dir . 'data_global.php'))
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			foreach ($this->var_expires as $var_name => $expires)
			{
				if (time() > $expires)
				{
					$this->destroy($var_name);
				}
			}
		}

		set_config('cache_last_gc', time(), true);
	}

	/**
	* Get saved cache object
	*/
	function get($var_name)
	{
		if ($var_name{0} == '_')
		{

			if (!$this->_exists($var_name))
			{
				return false;
			}

			include($this->cache_dir . 'data' . $var_name . '.php');
			return (isset($data)) ? $data : false;
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name{0} == '_')
		{

			if ($fp = @fopen($this->cache_dir . 'data' . $var_name . ".php", 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, "<?php\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\n\$data = unserialize('" . str_replace("'", "\\'", str_replace('\\', '\\\\', serialize($var))) . "');\n?".">");
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->var_expires[$var_name] = time() + $ttl;
			$this->is_modified = true;
		}
	}

	/**
	* Destroy cache data
	*/
	function destroy($var_name, $table = '')
	{

		if ($var_name == 'sql' && !empty($table))
		{
			$regex = '(' . ((is_array($table)) ? implode('|', $table) : $table) . ')';

			$dir = opendir($this->cache_dir);
			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}

				$fp = fopen($this->cache_dir . $entry, 'rb');
				$file = fread($fp, filesize($this->cache_dir . $entry));
				@fclose($fp);

				if (preg_match('#/\*.*?\W' . $regex . '\W.*?\*/#s', $file, $m))
				{
					@unlink($this->cache_dir . $entry);
				}
			}
			@closedir($dir);

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name{0} == '_')
		{
			@unlink($this->cache_dir . 'data' . $var_name . ".php");
		}
		else if (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);
			unset($this->var_expires[$var_name]);

			// We save here to let the following cache hits succeed
			$this->save();
		}
	}

	/**
	* Check if a given cache entry exist
	*/
	function _exists($var_name)
	{
		if ($var_name{0} == '_')
		{
			return file_exists($this->cache_dir . 'data' . $var_name . ".php");
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			if (!isset($this->var_expires[$var_name]))
			{
				return false;
			}

			return (time() > $this->var_expires[$var_name]) ? false : isset($this->vars[$var_name]);
		}
	}

	/**
	* Format an array to be stored on filesystem
	*/
	function format_array($array)
	{
		$lines = array();
		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$lines[] = "\n'$k' => " . $this->format_array($v);
			}
			else if (is_int($v))
			{
				$lines[] = "\n'$k' => $v";
			}
			else if (is_bool($v))
			{
				$lines[] = "\n'$k' => " . (($v) ? 'true' : 'false');
			}
			else
			{
				$lines[] = "\n'$k' => '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $v)) . "'";
			}
		}

		return 'array(' . implode(',', $lines) . ')';
	}

	/**
	* Load cached sql query
	*/
	function sql_load($query)
	{

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = sizeof($this->sql_rowset);

		if (!file_exists($this->cache_dir . 'sql_' . md5($query) . ".php"))
		{

			echo 'no existe el fichero de cache: ' . $this->cache_dir . 'sql_' . md5($query) . ".php<br />";
			return false;
		}

		@include($this->cache_dir . 'sql_' . md5($query) . ".php");

		if (!isset($expired))
		{
			return false;
			echo 'caducado';
		}
		else if ($expired)
		{
			@unlink($this->cache_dir . 'sql_' . md5($query) . ".php");
			echo 'caducado';
			return false;
		}

		return $query_id;
	}

	/**
	* Save sql query
	*/
	function sql_save($query, &$query_result, $ttl)
	{
		global $dbi;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		if ($fp = @fopen($this->cache_dir . 'sql_' . md5($query) . '.php', 'wb'))
		{
			@flock($fp, LOCK_EX);

			$lines = array();
			$query_id = sizeof($this->sql_rowset);
			$this->sql_rowset[$query_id] = array();

			while ($row = $dbi->sql_fetchrow($query_result))
			{
				$this->sql_rowset[$query_id][] = $row;

				$lines[] = "unserialize('" . str_replace("'", "\\'", str_replace('\\', '\\\\', serialize($row))) . "')";
			}
			$dbi->sql_freeresult($query_result);

			fwrite($fp, "<?php\n\n/*\n$query\n*/\n\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\n\$this->sql_rowset[\$query_id] = array(" . implode(',', $lines) . ') ?' . '>');
			@flock($fp, LOCK_UN);
			fclose($fp);

			$query_result = $query_id;
		}
	}

	/**
	* Ceck if a given sql query exist in cache
	*/
	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database)
	*/
	function sql_fetchrow($query_id)
	{
		return array_shift($this->sql_rowset[$query_id]);
	}
}

?>