<?php

	class NotifyMsgModel extends CommonModel {

		/**
	     * 消息状态
	     */
		const STATUS_CREATE = 0;		//消息被创建
		const STATUS_SENT_SUCCESS = 1;		//消息已发送成功	
		const STATUS_SENT_FAILURE = 2;		//消息已发送失败
		const STATUS_HUNGUP = 5;	//消息被挂起


		public function setMsgSendSucces(array $idList){
			if(empty($idList) || !is_array($idList)){
				return;
			}

			$where = [
				'id'=>['in',$idList]
				];

			$data = [
					'status'=>self::STATUS_SENT_SUCCESS,
					'send_time'=>time()
				];
			
			$result = $this->where($where)->save($data);
			if($result === false){
				return false;
			}

			return true;
		}


		public function setMsgSendFailure(array $idList){
			if(empty($idList) || !is_array($idList)){
				return;
			}

			$where = [
				'id'=>['in',$idList]
				];

			$data = [
					'status'=>self::STATUS_SENT_FAILURE,
					'send_time'=>time()
				];
			
			$result = $this->where($where)->save($data);
			if($result === false){
				return false;
			}

			return true;
		}

	}

?>