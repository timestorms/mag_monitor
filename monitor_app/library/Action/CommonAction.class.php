<?php
	class CommonAction extends Action 
	{		
		protected static $model = null; //数据模型model
		protected $arc_model = null;
		protected $theme;
		protected $items;
		public $lang = 'zh-cn';
		public $sessionID;
		public $userID;
		public $userInfo;
		
		function _initialize(){

			header("Content-type: text/html; charset=utf-8");

			//import('@.Utils.CacheUtil');
			import('@.Utils.Logs');
			import('@.Utils.Security');
			
			//检查语言
			if ( isset( $_COOKIE['think_language'] ) ){
				$this->lang = strtolower($_COOKIE['think_language']);
			}
	    }
	    

	    /**
	     * 数据写入队列
	     * @author weiguo.zhou
	     * @param string $name 队列名称
	     * @param string $value 值
	     * @version 0.0.1
	     */
	    public function insertList($name, $value) {
	    	$cache = Cache::getInstance();
	    	return $cache->rPush($name, $value);
	    }
	    
	    /**
	     * 数据取出队列
	     * @author weiguo.zhou
	     * @param string $name 队列名称
	     * @version 0.0.1
	     */
	    public function getList($name) {
	    	$cache = Cache::getInstance();
	    	//var_dump($cache);
	    	return $cache->lPop($name);
	    }


		Public function sendEmail($emailList = array() , $message= ""){
	    	if(empty($emailList)){
	    		return  false ;
	    	}
	     	vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
			header("content-type:text/html;charset=utf-8");
			ini_set("magic_quotes_runtime",0);
			try {
				$mailConfig = C('mail_config');
				if(empty($mailConfig)){
					log_message('get mail config error!info:'.json_encode($mailConfig),LOG_ERR);
					return;
				}
				$mail = new PHPMailer(true); 
				$mail->IsSMTP();
				$mail->CharSet= $mailConfig['charset']; //设置邮件的字符编码，这很重要，不然中文乱码
				$mail->SMTPAuth   = true;                  //开启认证
				$mail->Port       = $mailConfig['port'];                    
				$mail->Host       = $mailConfig['host']; 
				$mail->Username   = $mailConfig['username'];    
				$mail->Password   = $mailConfig['password'];            
				$mail->From       = $mailConfig['from'];
				$sendTo = array() ;
				foreach($emailList as $key=>$value){
					//检验邮件地址是否正确
					$mail->AddAddress($value);
				}

				//$mail->MsgHTML($message);

				$mail->FromName   = $mailConfig['fromname'];
				$mail->Subject  = $mailConfig['subject'];
				$mail->Body = "<h3>报警邮件</h3>\n".$message;
				//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; //当邮件不支持html时备用显示，可以省略
				$mail->AltBody    = $mailConfig['altbody'];
				//$mail->WordWrap   = 80; // 设置每行字符串的长度
				$mail->IsHTML(true); 
				if(!$mail->Send()){
					return false;
				}
				return true ;
			} catch (phpmailerException $e) {
				echo "邮件发送失败：".$e->errorMessage();
				log_message('send mail error!info:'.$e->errorMessage(),LOG_ERR);
				return false ;
			}
	    }

	    protected function http_succ($data = array(),$isReturn=false)
	    {        
	        $data = array (
	            'code' => 0,
	            'msg' => 'success',
	            'data' => $data 
	        );
	        $ret = $this->_getEncrypt($data);
	        if($isReturn){
	            return $ret;
	        }else{
	            echo $ret;
	            exit();
	        }
	    }

	    protected function http_fail($data = array(),$isReturn=false){        
	        $data = array (
	            'code' => -1,
	            'msg' => 'failure',
	            'data' => $data 
	        );
	        $ret = $this->_getEncrypt($data);
	        if($isReturn){
	            return $ret;
	        }else{
	            echo $ret;
	            exit();
	        }
	    }

	    /**
	     * 加密
	     * @param  [type] $data [description]
	     * @return [type]       [description]
	     */
	    private function _getEncrypt($data){	
	        return is_array($data) ? json_encode($data) : strval($data);
	    }

}

?>
