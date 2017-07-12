<?php

	class UserModel extends CommonModel {

		//存放用户信息的文件一天更新一次
		const FILE_ALICE_TIME = 86400;

		/**
	     * 用户状态
	     */
		const STATUS_FORBIDDEN = 0;
		const STATUS_NORMAL = 1;

	    public function getUserInfo(){
	        $result = $this->where(['status'=>self::STATUS_NORMAL])->select();
	        if($result === false){
	            return false;
	        }      
	        return $result;
	    }

	}

?>