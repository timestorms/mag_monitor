<?php

class MonitorAction extends CommonAction {

    const NOTIFY_TIME = 86400;   //只发送最近一天的消息

    const HUNGUP_TIME = 300;    //5分钟内挂起有效

    //挂起时长
    public static $hungType = array(
            '300' => '挂起5分钟',         //秒单位，5分钟
            '600' => '挂起10分钟',         
            '1800' => '挂起30分钟',
            '3600' => '挂起1小时',
            '7200' => '挂起2小时',
            '21600' => '挂起6小时',
            '43200' => '挂起12小时',
        );

    public function index(){
        exit('input method!');
    }

    public function testSend(){
        $arr = array('test@test.cn');
        $msg = 'one test';
        $result = $this->sendEmail($arr,$msg);
        if($result=== false){
            echo 'false';
        }else{
            echo 'success';
        }
        echo 'over';
    }
    /*
        1.获取所有需要发送的消息
        2.获取对应的消息所对应的email
        3.发送邮件
        4.需要将负责人和知会人email全拿到，并且去重
     */
    
    //获取即将要发送的邮件消息
    public function notifyMsg(){

        //获取需要发送的消息.近NOTIFY_TIME 时间的消息记录，未被挂起的
        $filed = 'n.id,n.project_name,n.content,n.create_time,n.sign,p.notify_people_id,p.inform_people_id';
        $join = ' left join '.C('DB_PREFIX').'project p on n.project_name=p.project_name ';
        $where = array(
                'n.create_time'=>array('gt',time() - self::NOTIFY_TIME),
                'n.status'=>NotifyMsgModel::STATUS_CREATE,
            );
        $emailMsgList = M('notify_msg n')->field($filed)->where($where)->join($join)->limit(50)->select();

        if($emailMsgList === false){
            log_message('get msglist error!info:'.json_encode($where),LOG_ERR);
            return false;
        }

        if(empty($emailMsgList)){
            log_message('no msg email to send!',LOG_INFO);
            return;
        }

        //获取目前被有效挂起的消息
        $hungupMsgModel = new HungupMsgModel();
        $hungupList = $hungupMsgModel->getHungupMsg();

        log_message('before emailList:'.json_encode($emailMsgList),LOG_DEBUG);

        //过滤掉被挂起的消息
        if(!empty($hungupList)){
            foreach($emailMsgList as $key=>$emailInfo){
                if(isset($hungupList[$emailInfo['sign']])){
                    unset($emailMsgList[$key]);
                }
            }
        }

        log_message('unset emailList:'.json_encode($emailMsgList),LOG_DEBUG);
        
        //获取所有用户的信息
        $allUserList = UserAction::getAllUser();
        if(empty($allUserList)){
            log_message('cant get userinfo!',LOG_ERR);
            exit('cant get userinfo!'); 
        }

        $successMsgIdList = $failureMsgIdList = [];


        //开始处理要发送的消息,多个邮件信息（一个邮件信息对应多个接收人）
        foreach($emailMsgList as $emailInfo){

            //获取收件人的邮箱
            $notifyPeople = $informPeople = array();
            if(isset($emailInfo['notify_people_id'])){
                $notifyPeople = explode(',',$emailInfo['notify_people_id']);
            }
            if(isset($emailInfo['inform_people_id'])){
                $informPeople = explode(',',$emailInfo['inform_people_id']);
            }
            $reciverIdList = array_merge($notifyPeople,$informPeople);
            $reciverIdList = array_unique($reciverIdList);

            if(empty($reciverIdList)){
                log_message('no reciver!info:'.json_encode($emailInfo),LOG_NOTICE);
                continue;
            }

            //获取消息邮件对应的接收人的邮件地址
            $emailList = $this->getEmailAddress($reciverIdList,$allUserList);
            if(empty($emailList)){
                log_message('get empty email!info:'.json_encode($reciverIdList),LOG_NOTICE);
                continue;
            }

            //生成挂起url和相关人信息
            $footStr = $this->generateMailFoot($emailInfo,$allUserList);
            $msg = $emailInfo['content'].$footStr;

            //开始发邮件
            $result = $this->sendEmail($emailList,$msg);
            if($result === false){
                $failureMsgIdList[] = $emailInfo['id'];
                log_message('send message failed!email:'.json_encode($emailList).',msg:'.$emailInfo['content'],LOG_ERR);
                continue;
            }

            //邮件发送成功，保存成功的消息id
            $successMsgIdList[] = $emailInfo['id'];
        }

        //回写消息的状态
        $notifyMsgModel = new NotifyMsgModel();
        if(!empty($failureMsgIdList)){
            $result = $notifyMsgModel->setMsgSendFailure($failureMsgIdList);
            if($result === false){
                log_message('update send msg-fail status failed!info:'.json_encode($failureMsgIdList),LOG_ERR);
            }
        }

        if(!empty($successMsgIdList)){
            $result = $notifyMsgModel->setMsgSendSucces($successMsgIdList);
            if($result === false){
                log_message('update send msg-success status failed!info:'.json_encode($successMsgIdList),LOG_ERR);
            }
        }

        log_message('finish send email! success:'.json_encode($successMsgIdList).','.json_encode($failureMsgIdList),LOG_INFO);
        exit('finish');
    }


    //从文件中获取，如果文件不存在就查询数据库，然后再写入文件
    public function getEmailAddress(array $userIdList,array $allUserList){
        if(empty($userIdList)){
            return array();
        }
        //$emailList = UserModel::getAllUser();

        //array_change_key($allUserList,'id');

        $result = array();

        foreach($userIdList as $userId){
            if(isset($allUserList[$userId])){
                $result[] = $allUserList[$userId]['email'];
            }
        }
        return $result;
    }


    //挂起一个消息
    public function hungupMsg(){
        if(!isset($_REQUEST['hungup']) || !isset($_REQUEST['sign']) ||!isset($_REQUEST['time']) || !isset($_REQUEST['id'])){
            exit('非法参数！');
        }

        $time = trim($_REQUEST['time']);
        $sign = trim($_REQUEST['sign']);

        //检验挂起时间是否合法
        if(!isset(self::$hungType[$time])){
            log_message('hung time error!info:'.json_encode($_REQUEST),LOG_ERR);
            exit('操作不合法！');   
        }

        $where = [
            'id'=>trim($_REQUEST['id']),
            'sign'=>$sign,
            //'status'=>NotifyMsgModel::STATUS_SENT_SUCCESS
        ];
        $msgInfo = M('notify_msg')->where($where)->order('id desc')->find();
        //无法查询到对应的消息是有异常的
        if(empty($msgInfo)){
            log_message('hung up error!info:'.json_encode($where),LOG_ERR);
            exit('非法操作！');
        }

        //只能挂起5分钟内发送的消息
        if((time()-intval($msgInfo['send_time']))>self::HUNGUP_TIME){
            log_message('hung up error!info:'.json_encode($where),LOG_ERR);
            exit('挂起链接失效！');
        }

        //将挂起的消息加入到挂起的数据表
        $hungupMsgModel = new HungupMsgModel();
        $hungupMsgModel->startTrans();
        $now = time();
        $data = [
                'sign'=>$sign,
                'end_time'=>intval($time)+$now,
                'create_time'=>$now
            ];
        $result = M('hungup_msg')->add($data);
        if($msgInfo === false){
            exit('挂起消息失败！');
        }

        //更新被挂起消息状态
        $result = M('notify_msg')->where(['sign'=>$sign])->save(['status'=>NotifyMsgModel::STATUS_HUNGUP,'hangon_time'=>$now]);
        if($result === false){
            $hungupMsgModel->rollback();
            exit('挂起(状态)失败！');
        }
        $hungupMsgModel->commit();
        exit('挂起成功！');
    }
    

    //接收各个项目发送来的消息
    public function notify(){
        if(!isset($_REQUEST['name']) || !isset($_REQUEST['msg'])){
            $this->http_fail('invalid paramsr!');
        }

        //是否要判断该脚本名合法
        $data =array(
                'project_name' =>trim($_REQUEST['name']),
                'content' =>trim($_REQUEST['msg']),
                'sign' =>trim(md5($_REQUEST['name'].$_REQUEST['msg'])),
                'create_time' => time()
            );

        $result = M('notify_msg')->add($data);
        if($result === false){
            $this->http_fail('operate db error!');
        }

        $this->http_succ($result);
    }

    public function generateMailFoot($mailInfo,array $allUserList){

        $userStr = '<BR><BR><b>负责人</b>：';
        $notifyIdList = $informIdList = $notifyNicknameList = $informNicknameList =[];
        if(!empty($mailInfo['notify_people_id'])){
            $notifyIdList = explode(',',$mailInfo['notify_people_id']);
            //log_message('notify:'.json_encode($notifyIdList),LOG_INFO);
            foreach($notifyIdList as $id){
                $notifyNicknameList[] = $allUserList[$id]['nickname'];
            }
        }
        if(!empty($mailInfo['inform_people_id'])){
            $informIdList = explode(',',$mailInfo['inform_people_id']);
            //log_message('inform:'.json_encode($informIdList),LOG_INFO);
            foreach($informIdList as $id){
                $informNicknameList[] = $allUserList[$id]['nickname'];
            }
        }

        //log_message('notify-n:'.json_encode($notifyNicknameList),LOG_INFO);

        //log_message('inform-n:'.json_encode($informNicknameList),LOG_INFO);
        
        $userStr.=implode(',',$notifyNicknameList).'<BR><b>知会人</b>：'.implode(',',$informNicknameList);


        //挂起url
        $monitorBaseUrl = C('monitor_url');

        $tempUrl = $monitorBaseUrl.'hungupMsg?hungup=1&id='.$mailInfo['id'].'&sign='.$mailInfo['sign'].'&time=';

        $hungupUrl = '<BR><BR>';
        foreach(self::$hungType as $key=>$desc){
            $hungupUrl.="<a href='{$tempUrl}{$key}'style='margin-right:20px;display:inline-block;''>$desc</a>";
        }

        return $userStr.$hungupUrl;
    }


    /*
        删除一天前已经发送的消息
    */
    public function cleanExpiredMsg(){
        $expiredTime = time() - 43200;
        $where = [
            'status'=>['in',[HungupMsgModel::STATUS_SENT_SUCCESS,HungupMsgModel::STATUS_SENT_FAILURE,HungupMsgModel::STATUS_HUNGUP]],
            'create_time'=> ['lt',$expiredTime]
        ];
        $result = M('notify_msg')->where($where)->delete();
        if($result === false){
            log_message('clean expired msg error!',LOG_ERR);
            return;
        }
        log_message('clean expired msg success!info:'.json_encode($result),LOG_INFO);
    }
}
?>

