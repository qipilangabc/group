<?php
class CMemCache
{
	/**
	 * @var boolean whether to use memcached or memcache as the underlying caching extension.
	 * If true {@link http://pecl.php.net/package/memcached memcached} will be used.
	 * If false {@link http://pecl.php.net/package/memcache memcache}. will be used.
	 * Defaults to false.
	 */
	public $useMemcached=false;
	/**
	 * @var Memcache the Memcache instance
	 */
	private $_cache=null;
	/**
	 * @var array list of memcache server configurations
	 */
	private $_servers=array(
	          /*
			   array(
			      'host'=>'10.6.197.101',
			      'port' => 11211,
				  'persistent'=>FALSE,
			   ),
			   */
			   array(
			      'host'=>'10.6.196.118',
				  'port'=>11211,
				  'persistent'=>FALSE,
			   ),
	);

	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It creates the memcache instance and adds memcache servers.
	 * @throws CException if memcache extension is not loaded
	 */
 
	public function __construct()
	{
		$cache=$this->getMemCache();
		if(count($this->_servers))
		{
			foreach($this->_servers as $server)
			{
				if($this->useMemcached)
					$cache->addServer($server['host'],$server['port'],$server['weight']);
				else
					$cache->addServer($server['host'],$server['port'],$server['persistent']);
			}
		}
		else
			$cache->addServer('localhost',11211);
	}

	/**
	 * @throws CException if extension isn't loaded
	 * @return Memcache|Memcached the memcache instance (or memcached if {@link useMemcached} is true) used by this component.
	 */
	private function getMemCache()
	{
		if($this->_cache!==null)
			return $this->_cache;
		else
		{
			$extension=$this->useMemcached ? 'memcached' : 'memcache';
			if(!extension_loaded($extension))
				throw new Exception("no ".$extension." extension to be loaded.");
			return $this->_cache=$this->useMemcached ? new Memcached : new Memcache;
		}
	}

	/**
	 * @return array list of memcache server configurations. Each element is a {@link CMemCacheServerConfiguration}.
	 */
	public function getServers()
	{
		return $this->_servers;
	}

	/**
	 * @param array $config list of memcache server configurations. Each element must be an array
	 * with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
	 * @see http://www.php.net/manual/en/function.Memcache-addServer.php
	 */
	public function setServers($config)
	{
		foreach($config as $c)
			$this->_servers[]=$c;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function get($key)
	{
		return $this->_cache->get($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	public function getValues($keys)
	{
		return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($key,$value,$expire=0)
	{
		if($expire>0)
			$expire+=time();
		else
			$expire=0;

		return $this->useMemcached ? $this->_cache->set($key,$value,$expire) : $this->_cache->set($key,$value,0,$expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($key,$value,$expire=0)
	{
		if($expire>0)
			$expire+=time();
		else
			$expire=0;

		return $this->useMemcached ? $this->_cache->add($key,$value,$expire) : $this->_cache->add($key,$value,0,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	public function delete($key)
	{
		return $this->_cache->delete($key, 0);
	}
    /*
	 * set inc value
	 *  @param string $key    
	 *  @param $expire
	 *  @return int 
	 */
	 public function increment($key,$expire=3600)
	{
	    if( $this->_cache->get($key) === FALSE)
		{
		      return $this->useMemcached ? $this->_cache->set($key,1,$expire) : $this->_cache->set($key,1,0,$expire);
		}
		return $this->_cache->increment($key);
	}
	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 * @since 1.1.5
	 */
	public function flush()
	{
		return $this->_cache->flush();
	}
}

?>