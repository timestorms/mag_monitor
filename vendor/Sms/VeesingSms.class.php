<?php

//namespace vendor\Sms;
class VeesingSms {

    public function __construct($config = '') {
        $this->config = $config;
    }

    /**
     * 发送短信
     * @param int|str $mobile 手机号码（最多100个），多个用英文逗号(,)隔开
     * @param str $content 短信内容（最多300个汉字），特殊字符处理：%请使用中文％代替；
     * 						 如果使用模板短信发送，此参数用来传递模板短信的变量和值。
     * @param int $tempid 模板号
     * @param int $time 发送时间,格式为:年年年年月月日日时时分分秒秒,例如:20090504111010.为空时，为即时发送短信
     * @return array array(0,数字,数字) 提交成功，格式：array(返回值,提交计费条数,提交成功号码数)
     */
    public function send($mobile, $content, $tempid = 1, $time = null) {
        header("Content-type:text/html;charset=utf-8");
        switch ($tempid) {
            case 1:
                $cont = " {$content}，欢迎您的加入。如非本人操作，请忽略本信息。";
                break;
            case 3:
                $cont = "您的商品已于{$content[0]}发出，由{$content[2]}承运，物流单号{$content[1]}，如需帮助请致电4000887310。";
                break;
            case 4:
                $cont = "您好！您退回的订单尾号为{$content}的商品我们已收到！我们会在5个工作日左右退款到您付款账户！如有疑问，请联系400-088-7310。";
                break;
            case 5:
                $cont = "您好！您退回的订单尾号为{$content}的商品我们已收到！我们会尽快为您发送新的商品！如有疑问，请联系400-088-7310。";
                break;
            case 6:
                $cont = "亲爱的{$content[0]}童鞋，您的手机号对应获得的代码是{$content[1]} 记得关注相应时段公布获奖名单。";
                break;
            case 7:
                $cont = "您的订单尾号为{$content}已经支付完成，我们将于今天为您发货（节假日顺延），如需帮助请致电4000887310";
                break;
            case 8:
                $cont = "您的订单尾号为{$content}已经支付完成，我们将于明天为您发货（节假日顺延），如需帮助请致电4000887310";
                break;
            case 9:
                $cont = "您的订单尾号为{$content}有异常，客服将在1-3个工作日和您联系并办理售后事宜，您也可以直接致电4000887310";
                break;
            case 10:
                $cont = "您的订单尾号为{$content}的物流信息长时间未更新，已经安排客服人员为您查询订单最新情况，稍后会有客服人员和您联系，请耐心等待，您也可以直接致电4000887310";
                break;
            case 11:
            	 $cont = "卖客疯欢迎你，登录密码：{$content}，如非本人请忽略此条信息";
                break;
            case 12:
            	$cont = "您的登录校验码：【{$content}】，欢迎您的加入。如非本人操作，请忽略本信息。";
                break;
        }
        $param = array(
            'account' => $this->config['SMS_USERNAME'],
            'password' => $this->config['SMS_SCODE'],
            'mobile' => $mobile,
            'content' => $cont
        );
        $post_data = http_build_query($param);
        $return_str = Sms::curl($post_data, $this->config['SMS_URL']);
        $SubmitResult = Sms::xml_to_array($return_str);
        if($SubmitResult['SubmitResult']['code'] == 2){
            return TRUE;
        }
        return FALSE;
    }

}
