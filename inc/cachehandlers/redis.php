<?php
/**
 * Redis Cache Handler written for MyBB 1.6 by Cameron:D
 * 
 * Repository: https://github.com/Cameron-D/MyBB-Redis-Cache
 * 
 * Version 0.9
 */

class redisCacheHandler
{
	/**
	 * The redis server resource
	 */
	public $redis;

	/**
	 * Unique identifier representing this copy of MyBB
	 */
	public $unique_id;
	
	function redisCacheHandler($silent=false)
	{
		global $mybb;
		
		// Check if our DB engine is loaded
		if(!extension_loaded("redis"))
		{
			// Throw our super awesome cache loading error
			$message = "The phpredis module does not appear to be installed or enabled. It is required to use the redis cachehandler";
			$error_handler->trigger($message, MYBB_CACHEHANDLER_LOAD_ERROR);
			die;
		}
	}

	/**
	 * Connect and initialize this handler.
	 *
	 * @return boolean True if successful, false on failure
	 */
	function connect()
	{
		global $mybb, $error_handler;
		
		$this->redis = new Redis();
		
		if(!$mybb->config['redis']['host'])
		{
			$message = "Please configure the redis settings in inc/config.php before attempting to use this cache handler";
			$error_handler->trigger($message, MYBB_CACHEHANDLER_LOAD_ERROR);
			die;
		}
	
		if(!$mybb->config['redis']['port'])
		{
			$mybb->config['redis']['port'] = "6379";
		}

		try {
			//Check if it is a unix socket, although only supports absolute path
			if(substr($mybb->config['redis']['host'], 0, 1) === "/")
			{
				$this->redis->connect($mybb->config['redis']['host']);
			}
			else
			{
				$this->redis->connect($mybb->config['redis']['host'], $mybb->config['redis']['port']);
			}
		} catch (RedisException $e) {
			$message = "Unable to connect to the redis server configured in inc/config.php. Are you sure it is running?";
			$error_handler->trigger($message, MYBB_CACHEHANDLER_LOAD_ERROR);
			die;
		}
		
		//Is a password set?
		if(isset($mybb->config['redis']['auth']))
		{
			$redis->auth($mybb->config['redis']['auth']);
		}

		// Set a unique identifier for all queries in case other forums are using the same server
		$this->unique_id = md5(MYBB_ROOT);

		return true;
	}
	
	/**
	 * Retrieve an item from the cache.
	 *
	 * @param string The name of the cache
	 * @param boolean True if we should do a hard refresh
	 * @return mixed Cache data if successful, false if failure
	 */
	
	function fetch($name, $hard_refresh=false)
	{
		$data = $this->redis->get("mybb:".$this->unique_id.":".$name);

		if($data === false)
		{
			return false;
		}
		else
		{
			return unserialize($data);
		}
	}
	
	/**
	 * Write an item to the cache.
	 *
	 * @param string The name of the cache
	 * @param mixed The data to write to the cache item
	 * @return boolean True on success, false on failure
	 */
	function put($name, $contents)
	{
		return $this->redis->set("mybb:".$this->unique_id.":".$name, serialize($contents));
	}
	
	/**
	 * Delete a cache
	 *
	 * @param string The name of the cache
	 * @return boolean True on success, false on failure
	 */
	function delete($name)
	{
		if($this->redis->delete("mybb:".$this->unique_id.":".$name))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Disconnect from the cache
	 */
	function disconnect()
	{
		@$this->redis->close();
	}
	
	function size_of($name)
	{
		global $lang;
		
		return $lang->na;
	}
}

?>