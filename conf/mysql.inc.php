<?php

	 return array(
		'DB_TYPE'               => 'mysql',     // 数据库类型
    	'DB_DEPLOY_TYPE'        => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    	'DB_RW_SEPARATE'        => false,       // 数据库读写是否分离 主从式有效
		'DB_HOST'               => '192.168.1.1', // 服务器地址
		'DB_NAME'               => 'db_monitor',          // 数据库名
		'DB_USER'               => 'user',      // 用户名
		'DB_PWD'                => '123456',          // 密码
		'DB_PORT'               => '3306',        	// 端口
		'DB_PREFIX'             => 'monitor_',    // 数据库表前缀
	  	'DB_FIELDTYPE_CHECK'    => false,       // 是否进行字段类型检查
	  	'DB_FIELDS_CACHE'       => false,        // 启用字段缓存
	  	'DB_CHARSET'            => 'utf8',      // 数据库编码默认采用utf8
	);
        
?>
