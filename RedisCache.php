<?php
class RedisCache{
      private $_link = NULL;
	  private  $driver;
	  private $_debug = false;
      public function __construct($host=null,$port=6379,$persistent=false,$timeout=15){
	   try{
	    if (!extension_loaded('redis')) {
    	       throw new Exception('no redis module');		
		}
	    $this->driver = new Redis();
		if(!empty($this->_link)) return;
		    $connectType = $persistent ? 'pconnect' : 'connect';
		if ($host)
		{
			$this->_link = $this->driver->$connectType ( $host, $port, $timeout );
		} else
		{
			$this->_link = $this->driver->$connectType ( 'localhost', 6379 );
		}
		}catch(Exception $e){
		           throw new Exception($e->getMessage());
		}
	  }
	  
	    /**
     * 写入缓存
     *
     * @param string $key 组存KEY
     * @param array $value 缓存值(array("key"=>"value"));
     * @param int $expire 过期时间， 0:表示无过期时间
     */
    public function setArray($key, $value, $expire = 0)
    {
        $value = serialize ( $value ); //序列化
        if ($expire == 0)
        { // 永不超时
            $res = $this->driver->set ( $key, $value );
        } else
        {
            $res = $this->driver->setex ( $key, $expire, $value );
        }
        return $res;
    }
    
    /**
     * 读缓存获得数组
     *
     * @param string $key 缓存KEY,
     * @return array || boolean  失败返回 false, 成功返回数组 
     */
    public function getArray($key)
    {
        // 没有使用M/S
        $res = $this->driver->get ( $key );
        $res = unserialize ( $res ); //反序列话
        if (is_array ( $res )&&! empty ( $res ) && count ( $res ) > 0)
        {
          
                return $res;
          }else{// 为空或者不是失败都返回flase
            
              return false;
        }
    
    } 
	  
	 public function __call($method, $params)
     {
	   
		     if(!method_exists($this->driver,$method)){
			     throw new Exception('类方法不存在');
			 }else{
			    $reflectionMethod = new ReflectionMethod($this->driver, $method);
                return $reflectionMethod->invokeArgs($this->driver, $params);
			    //return call_user_func_array ( array ($this->driver, $method ), $params );
			 }
		     
        //
       
	 }
	 public function debug(){
		  $this->methods();
	 }
	 
	 private function methods(){
	     $rc = new ReflectionClass($this->driver);
		 $ms = $rc->getMethods();
		 foreach($ms as $m){
		    echo '<p>类:'.$m->class.' 方法 '.$m->name.'()</p>';
		 }
	 }
}
?>