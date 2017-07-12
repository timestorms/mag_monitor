<?php

/**

 * 缓存类，目前只使用Redis

 * @author weiguo.zhou

 * @version 0.0.1

 */

class CacheUtil {

	/**

	 * Redis 服务器连接对象

	 *

	 * @var	Redis

	*/

	protected $_redis;

	

	/**

	 * 当前的类对象

	 * @var Cache_redis

	 */

	private static $cache_object = null;

	/**

	 * 序列化Key数组

	 * @var array

	 */

	protected $_serialized = array();

	// ------------------------------------------------------------------------

	/**

	 * 从缓存中获得指定Key的值 ,如果取出的值为序列化后的值，将执行一次反序列化还原对象

	 * @author weiguo.zhou

	 * @param string $key

	 * @return mixed 返回从缓存中取出的数据

	 * @version 0.0.1

	 */

	public function get($key){

		$value = $this->_redis->get($key);

		if ($this->is_serialized($value)){

			return unserialize($value);

		}

		return $value;

	}

	// ------------------------------------------------------------------------

	/**

	 * 将批定的Key及其数据加入到缓存中。如要存入的数据为数组或对象，将执行序列化

	 * 在取出数据时要执行反序列化操作

	 * @author weiguo.zhou

	 * @param string $id   缓存内的Key值

	 * @param mixed $data 缓存内的数据

	 * @param int $ttl TTL时间

	 * @param boolean 保存成功返回true否则返回false

	 * @version 0.0.1

	 */

	public function set($id, $data, $ttl, $raw = FALSE){

		if(is_array($data) || is_object($data)){

			$data = serialize($data);

		}

		return ($ttl)? $this->_redis->setex($id, $ttl, $data): $this->_redis->set($id, $data);

	}

	// ------------------------------------------------------------------------

	/**

	 * 删除指定的Key的值

	 * @author weiguo.zhou

	 * @param string $key

	 * @return boolean 成功返回true,否则返回false

	 * @version 0.0.1

	 */

	public function delete($key){

		if ($this->is_key_exists($key)){

			if ($this->_redis->delete($key) !== 1){

				return FALSE;

			}

		}

		return TRUE;

	}

	// ------------------------------------------------------------------------

	/**

	 * Increment a raw value

	 *

	 * @param	string $id Cache ID

	 * @param	int $offset Step/value to add

	 * @return	mixed New value on success or FALSE on failure

	 */

	public function increment($id, $offset = 1){

		return $this->_redis->incr($id, $offset);

	}

	// ------------------------------------------------------------------------

	/**

	 * Decrement a raw value

	 *

	 * @param	string $id Cache ID

	 * @param	int $offset Step/value to reduce by

	 * @return	mixed New value on success or FALSE on failure

	 */

	public function decrement($id, $offset = 1){

		return $this->_redis->decr($id, $offset);

	}

	// ------------------------------------------------------------------------

	/**

	 * 清空当前Redis缓存内的全部数据

	 * @author weiguo.zhou

	 * @version 0.0.1

	 */

	public function clean(){

		return $this->_redis->flushDB();

	}

	// ------------------------------------------------------------------------

	/**

	 * 获得连接的Redis服务器的信息

	 * @author weiguo.zhou

	 * @param string $type

	 * @version 0.0.1

	 */

	public function cache_info($type = NULL){

		return $this->_redis->info();

	}

	

	/**

	 * 验证指定的Key是否存在于缓存中

	 * @author weiguo.zhou

	 * @param string $key

	 * @version 0.0.1

	 */

	public function is_key_exists($key){

		return $this->_redis->exists($key);

	}

	// ------------------------------------------------------------------------

	/**

	 * 获得指定Key的 expire ttl data属性

	 * @author weiguo.zhou

	 * @param string $key

	 * @return multitype:number mixed |boolean 成功返回数据的数组，否则返回false

	 * @version 0.0.1

	 */

	public function get_metadata($key){

		$value = $this->get($key);

		if ($value){

			return array(

					'expire' => time() + $this->_redis->ttl($key),

					'data' => $value

			);

		}

		return FALSE;

	}

	// ------------------------------------------------------------------------

	/**

	 * 测试当前PHP环境是否支持Redis

	 * @author weiguo.zhou

	 * @return boolean

	 * @version 0.0.1

	 */

	public function is_supported(){

		if (extension_loaded('redis')){

			return $this->_setup_redis();

		}else{

			//log_message('debug', 'The Redis extension must be loaded to use Redis cache.');

			return FALSE;

		}

	}

	// ------------------------------------------------------------------------

	/**

	 * 当前类的构造方法，此方法使用的单例模式

	 * @author weiguo.zhou

	 * @return boolean

	 * @version 0.0.1

	 */

	private function __construct(){

		$config = C('MONITOR_REDIS_CONFIG');

		$this->_redis = new Redis();

		try{

			if ($config['socket_type'] === 'unix'){

				$success = $this->_redis->connect($config['socket']);

			}else{

				$success = $this->_redis->connect($config['host'], $config['port'], $config['timeout']);

			}

			if ( ! $success){

				//log_message('debug', 'Cache: Redis connection refused. Check the config.');

				return FALSE;

			}

		}catch (RedisException $e){

			//log_message('debug', 'Cache: Redis connection refused ('.$e->getMessage().')');

			return FALSE;

		}

		if (isset($config['password'])){

			$this->_redis->auth($config['password']);

		}

		// Initialize the index of serialized values.

		$serialized = $this->_redis->sMembers('_ci_redis_serialized');

		if ( ! empty($serialized)){

			$this->_serialized = array_flip($serialized);

		}

		return TRUE;

	}

	

	/**

	 * 取出当前类的实例

	 * @author weiguo.zhou

	 * @return CacheUtil

	 * @version 0.0.1

	 */

	public static function getInstance(){

		if(self::$cache_object == null){

			self::$cache_object = new CacheUtil();

		}

		return self::$cache_object;

	}

	

	/**

	 * 销毁当前的Redis对象，并关闭Redis连接

	 * @author weiguo.zhou

	 * @version 0.0.1

	 */

	public function __destruct(){

		if ($this->_redis){

			$this->_redis->close();

		}

	}

	

	/**

	 * 验证当前取出的值是否为序列化后的数据

	 * @author weiguo.zhou

	 * @param mixed $data

	 * @return boolean 如果数据为序列化的数据返回true，否则返回false

	 * @version 0.0.1

	 */

	public function is_serialized( $data ) {

		$data = trim( $data );

		if ( 'N;' == $data )

			return true;

		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )

			return false;

		switch ( $badions[1] ) {

			case 'a' :

			case 'O' :

			case 's' :

				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )

					return true;

				break;

			case 'b' :

			case 'i' :

			case 'd' :

				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )

					return true;

				break;

		}

		return false;

	}

}

?>