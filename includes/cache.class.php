<?php

class Cache
{
	const TYPE_NONE = 0;
	const TYPE_APC = 1;
	const TYPE_MEMCACHE = 2;

	public function __construct($type) {
		$this->type = $type;

		if ($this->type == self::TYPE_MEMCACHE) {
			global $impresslist_memcacheServer;
			global $impresslist_memcachePort;
			$this->memcache = new Memcache();
			$this->memcache->connect($impresslist_memcacheServer, $impresslist_memcachePort);// or die ("Could not connect to memcache server.");
		}
	}
	public function get($name) {
		if ($this->type == self::TYPE_NONE) {
			return false;
		} else if ($this->type == self::TYPE_APC) {
			$success = false;
			$data = apc_fetch($name, $success);
			if ($success == FALSE) { return false; }
			return $data;
		} else if ($this->type == self::TYPE_MEMCACHE) {
			return $this->memcache->get($name);
		}
	}
	public function set($name, $data, $timeout) {
		if ($this->type == self::TYPE_NONE) {
			return true;
		} else if ($this->type == self::TYPE_APC) {
			apc_add($name, $data, $timeout);
		} else if ($this->type == self::TYPE_MEMCACHE) {
			$this->memcache->set($name, $data, false, $timeout);
		}
	}

	public static $s_instance = null;
	public static function getInstance() 
	{
		if (self::$s_instance == null) 
		{ 
			global $impresslist_cacheType;
			self::$s_instance = new self($impresslist_cacheType);
		}
		return self::$s_instance;
	}


}

?>