<?php
if (!defined("APP_NAME"))
    exit();

$config = require ("../conf/config.inc.php");
$mysql = require ("../conf/mysql.inc.php");
$mail = require ("../conf/mail.inc.php");

$array = array(
    'LOAD_EXT_FILE' =>'array',
    'show_page_trace' => false,
    'URL_DISPATCH_ON' => 1,
    'URL_MODEL' => 1,
    'AUTO_BUILD_HTML' => 0,
    'USER_AUTH_ON' => true,
    'USER_AUTH_TYPE' => 1, // 默认认证类型 1 登录认证 2 实时认证
    'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式
    'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模块
    'NOT_AUTH_ACTION' => 'view', // 默认无需认证操作
    'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操作
    'GUEST_AUTH_ON' => false, // 是否开启游客授权访问
    'GUEST_AUTH_ID' => 0, // 游客的用户ID
    'LIKE_MATCH_FIELDS' => 'title|remark',
    'TAG_NESTED_LEVEL' => 3,

	'URL_CASE_INSENSITIVE' =>true,

	'MONITOR_REDIS_CONFIG' => array(
			'socket_type' => 'tcp',
			'host' => '10.8.4.233',
			'password' => 'redis123',
			'port' => 6379,
			'timeout' => 0
	),//系统的Redis配置
		
		
	'DATA_CACHE_TIME' => 0, //长连接时间,REDIS_PERSISTENT为1时有效
	'DATA_CACHE_PREFIX' => '', //缓存前缀
	'DATA_CACHE_TYPE' => 'Redis', //数据缓存类型
	'DATA_EXPIRE' => 0, //数据缓存有效期(单位:秒) 0表示永久缓存
	'DATA_PERSISTENT' => 1, //是否长连接
	'DATA_REDIS_HOST' => '10.8.4.233,10.8.4.234', //分布式Redis,默认第一个为主服务器
	'DATA_REDIS_PORT' => '6379', //端口,如果相同只填一个,用英文逗号分隔
	'DATA_REDIS_AUTH' => 'redis123', //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔

);

return array_merge($array,$config, $mysql,$mail);
?>