<?php

class UserAction extends CommonAction {

    private $userModel = null;


    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function index(){
        exit('input method!');
    }

    public static function getAllUser(){
        $userPath = C('user_file');
        //log_message('allUserInfoPath:'.$userPath,LOG_INFO);
        
        //判断文件的是否超过1天
        if(is_file($userPath) && ((time() - filemtime($userPath))<UserModel::FILE_ALICE_TIME)){
            //var_dump(date('Y-m-d H:i:s',filemtime($userPath))); 
            $content = file_get_contents($userPath);
            //log_message('content:'.$content,LOG_INFO);
            return json_decode($content,true);
        }
        $result = M('user')->where(['status'=>UserModel::STATUS_NORMAL])->select();
        if($result === false){
            return false;
        }

        //将id作为key
        array_change_key($result,'id');

        //将数据写入到缓存文件中
        $fp = file_put_contents($userPath,json_encode($result));
        if(!$fp){
            log_message('file_put_contents user_info.json error!path:'.$userPath,LOG_ERR);
        }
        
        return $result;
    }

    public function refreshUserInfo(){
        $filePath = C('user_file');
        $allUserList = $this->userModel->getUserInfo();
        array_change_key($allUserList,'id');
        $contents = json_encode($allUserList);
        if(!file_put_contents($filePath,$contents)){
            exit("refresh fail!");
        }else{
            exit("refresh success!\ncontents:".$contents);
        }
    }
}
?>

