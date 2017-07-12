<?php

	class HungupMsgModel extends CommonModel {

		/**
	     * 消息状态
	     */
		const STATUS_CREATE = 0;		//消息被创建
		const STATUS_SENT_SUCCESS = 1;		//消息已发送成功	
		const STATUS_SENT_FAILURE = 2;		//消息已发送失败
		const STATUS_HUNGUP = 5;	//消息被挂起


		//找出已经被挂起的消息
		public function getHungupMsg(){

	        $where = array('end_time'=>array('gt',time()));
	        $hungupMsg = $this->field('sign')->where($where)->select();

	        if($hungupMsg === false){
	            log_message('gethungupmsg err:'.json_encode($hungupMsg),LOG_ERR);
	            return false;
	        }

	        if(empty($hungupMsg)){
	            return array();
	        }


	        //将sign字段取出重组以为数组
	        $hungupMsg = array_get_column($hungupMsg,'sign');

	        //log_message('sign:'.json_encode($hungupMsg),LOG_INFO);
	        return array_flip($hungupMsg);
	    }

	}

?>