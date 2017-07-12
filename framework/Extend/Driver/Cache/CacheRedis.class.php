<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @category   Think
 * @package  Cache
 * @subpackage  Driver
 * @author guizhiming <sd2536888@163.com>
 */
class CacheRedis extends Cache {
    
    //读方法
    protected $write_method = array('append', 'decr', 'decrBy', 'getSet', 'incr', 'incrBy', 'incrByFloat', 'mSet', 'mSetNX', 'set', 'setBit',
        'setex', 'psetex', 'setnx', 'setRange',
        'del', 'delete', 'expire', 'setTimeout', 'pexpire', 'expireAt', 'pexpireAt', 'migrate', 'move', 'persist', 'rename', 'renameKey',
        'renameNx', 'restore');
    
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options)) {
            $options = array (
                'host'          => C('DATA_REDIS_HOST') ? C('DATA_REDIS_HOST') : '127.0.0.1',
                'port'          => C('DATA_REDIS_PORT') ? C('DATA_REDIS_PORT') : 6379,
                'timeout'       => C('DATA_CACHE_TIME') ? C('DATA_CACHE_TIME') : false,
                'persistent'    => C('DATA_PERSISTENT') ? C('DATA_PERSISTENT') : false,
				'auth'			=> C('DATA_REDIS_AUTH') ? C('DATA_REDIS_AUTH') : false,
            );
        }
		$options['host'] = explode(',', $options['host']);
		$options['port'] = explode(',', $options['port']);
		$options['auth'] = explode(',', $options['auth']);
		foreach ($options['host'] as $key=>$value) {
			if (!isset($options['port'][$key])) {
				$options['port'][$key] = $options['port'][0];
			}
			if (!isset($options['auth'][$key])) {
				$options['auth'][$key] = $options['auth'][0];
			}
		}
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire']) ?  $options['expire']  :   C('DATA_EXPIRE');
        $this->options['prefix'] =  isset($options['prefix']) ?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
        $this->options['length'] =  isset($options['length']) ?  $options['length']  :   0;
        $this->handler  = new \Redis;
    }
	
	/**
	 * 连接Redis服务端
	 * @access public
	 * @param bool $is_master : 是否连接主服务器
	 */
	public function connect($is_master = true) {
		if ($is_master) {
			$i = 0;
		} else {
			$count = count($this->options['host']);
			if ($count == 1) {
				$i = 0;
			} else {
				$i = rand(1, $count - 1);	//多个从服务器随机选择
			}
		}
		$func = $this->options['persistent'] ? 'pconnect' : 'connect';
		$this->options['timeout'] === false ?
            $this->handler->$func($this->options['host'][$i], $this->options['port'][$i]) :
            $this->handler->$func($this->options['host'][$i], $this->options['port'][$i], $this->options['timeout']);
		if ($this->options['auth'][$i]) {
			$this->handler->auth($this->options['auth'][$i]);
		}
	}

	/**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
		self::connect(false);
        N('cache_read',1);
        
        $value = $this->handler->get($this->options['prefix'].$name);
        
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
		self::connect(true);
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && $expire > 0) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
		self::connect(true);
        if (is_array($name)) {
            foreach ($name as $value) {
                $data[] = $this->options['prefix'].$value;
            }
        } else {
            $data = $this->options['prefix'].$name;
        }
        return $this->handler->delete($data);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
		self::connect(true);
        return $this->handler->flushDB();
    }

	/**
	 * 关闭长连接
	 * @access public
	 */
	public function __destruct() {
		if ($this->options['persistent'] == 'pconnect') {
			$this->handler->close();
		}
	}
    
    /**
     * 切换db,暂未切换成功
     * @param type $no
     */
    public function select($no = 0){
        self::connect(true);
        $this->handler->select($no);
    }
    
    /**
     * 根据键名获取键
     * @access public
     * @param string $name 键名
     * @return mixed
     */
    public function keys($name) {
		self::connect(false);
        $value = $this->handler->keys($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }
    
    /**
     * 向队列中插入一个值
     * @param type $key 队列名
     * @param type $value 值
     * @return int 返回当前值得顺序
     */
    public function rPush($key, $value){
        self::connect(true);
        $result = $this->handler->rPush($key, $value);
        return $result;
    }
    
    /**
     * 向队列中取出一个值
     * @param type $key 队列名
     * @return type
     */
    public function lPop($key) {
        self::connect(true);
        $result = $this->handler->lPop($key);
        return $result;
    }
    
    /**
     * 队列长度
     * @param type $key
     * @return type
     */
    public function lSize($key) {
        self::connect(false);
        $result = $this->handler->lSize($key);
        return $result;
    }
    
    public function __call($method, $args) {
        if (method_exists($this->handler, $method)) {
            if (in_array($method, $this->write_method)) {
                self::connect(true);
            } else {
                self::connect(false);
            }
            return call_user_func_array(array($this->handler, $method), $args);
        } else {
            E(__CLASS__ . ':' . $method . L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
}
