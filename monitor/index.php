<?php
	//调试模式
	define( 'APP_DEBUG',		false );
	define( 'NO_CACHE_RUNTIME',	true );
	
	//主配置项目
	define( 'SYSTEM_VERSION_MKF','V0.0.1' );									// 系统版本
	define( 'APP_NAME', 		'monitor_app' );									// 项目名称
	//define( 'THEME_I_NAME',		'themes' );									// 模板根目录名称
	define( 'APP_PATH', 		'../' . APP_NAME . '/' );					// 项目目录
	define( 'COMMON_PATH', 		APP_PATH . 'common/' );						// 项目公共目录
	define( 'LIB_PATH', 		APP_PATH . 'library/' );					// 项目类库目录
	define( 'CONF_PATH', 		APP_PATH . 'configs/' );					// 项目配置目录
	define( 'LANG_PATH', 		APP_PATH . 'language/' );					// 项目语言包目录
	define( 'APP_DATA_PATH', 	APP_PATH . 'data/');						// 项目临时文件主目录
	define( 'TMPL_PATH', 		APP_PATH . 'themes/' );			// 项目模板目录
	//define( 'TMPL_PATH', 		'../themes/' );			// 项目模板目录
	define( 'HTML_PATH', 		'../static/' );						// 项目静态目录
	define( 'RUNTIME_PATH', 	'../runtime/' );						// 项目临时文件主目录
	
	//网站缓存配置
	define( 'LOG_PATH', 		RUNTIME_PATH . 'logs/' );					// 项目日志目录
	define( 'TEMP_PATH', 		RUNTIME_PATH . 'temp/' );					// 项目缓存目录
	define( 'DATA_PATH', 		RUNTIME_PATH . 'data/' );					// 项目数据目录
	define( 'CACHE_PATH', 		RUNTIME_PATH . 'cache/' );					// 项目模板缓存目录

	define( 'VENDOR_PATH',		'../vendor/');								//加载verdor
	
	//日志记录配置
	if (!defined('DS')) {
	    define('DS', '/');
	}
	if(!defined('APP_PATH_LOG')){
		define('APP_PATH_LOG', LOG_PATH);
	}
	if(!defined('LOG_LEVEL')){
		define('LOG_LEVEL', LOG_DEBUG);
	}

	//运行项目
	require ( '../framework/ThinkPHP.php' );
?>