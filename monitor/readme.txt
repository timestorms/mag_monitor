//所有的定时任务都在这里设置
http://10.8.34.19:9000/task/list


//定时扫描并且发送邮件（每分钟一次）
http://monitor.test.com/index.php/monitor/notifyMsg


//添加新用户后，刷新新用户列表
http://monitor.test.com/index.php/user/refreshUserInfo


//每天清理已经发送的消息（每天早上10点）
http://monitor.test.com/index.php/monitor/cleanExpiredMsg
