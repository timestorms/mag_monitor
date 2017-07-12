<?php
return array(

    'monitor_url'=>'http://alert.monitor.com/index.php/monitor/',
    

    //存放用户信息的文件地址
    'user_file'=> APP_DATA_PATH . 'user_info.json',

    'app_status' => 'debug',
    'show_page_trace'			=> false,
    'default_module' => 'Index',
    'session_atuo_start' => true,
    'APP_FILE_CASE' => true,
    'APP_AUTOLOAD_PATH' => 'ORG.Util',
    'DB_FIELDTYPE_CHECK' => true,
    'TMPL_STRIP_SPACE' => true,
    'VAR_PAGE' => 'p',
    'URL_CASE_INSENSITIVE' => true,
    'TOKEN_ON' => false,

	//命令执行目录
	'PHPCMD_PATH'=>'/usr/local/php5/bin/php /data/cbdroot/sbin/cli.php',

);
