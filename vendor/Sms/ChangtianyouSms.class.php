<?php

namespace vendor\Sms;

class ChangtianyouSms{
	public function __construct($config = '') {
		$this->config = $config;
		dump($config);
	}
	public function send($mobile, $content, $tempid = 1, $time = null){
		header("Content-type:text/html;charset=gb2312");
		switch ($tempid) {
			case 1:
				$cont = "{$content}，欢迎您的加入【卖客疯】";
				break;
			case 3:
				$cont = "您的商品已于{$content[0]}发出，由{$content[2]}承运，物流单号{$content[1]}，如需帮助请致电4000887310【卖客疯】";
				break;
			case 4:
				$cont = "您好！您退回的订单尾号为{$content}的商品我们已收到！我们会在5个工作日左右退款到您付款账户！如有疑问，请联系400-088-7310【卖客疯】";
				break;
			case 5:
				$cont = "您好！您退回的订单尾号为{$content}的商品我们已收到！我们会尽快为您发送新的商品！如有疑问，请联系400-088-7310【卖客疯】";
				break;
			case 6:
				$cont = "亲爱的{$content[0]}童鞋，您的手机号对应获得的代码是{$content[1]} 记得关注相应时段公布获奖名单【卖客疯】";
				break;
		}
		$params = array(
			'un' => $this->config['un'],
			'pwd' => $this->config['pwd'],
			'mobile' => $mobile,
			'msg' => iconv('utf-8', 'gb2312', $cont),
		);
	   $post_data = http_build_query($params);
		
//		foreach ($params as $key => $val){
//		   $content.= $key."=".$val."&";
//		}
//		
	    $contents = Sms::curl($post_data, $this->config['SMS_URL']);
		$xml = simplexml_load_string($contents);
		if ($xml->State == 0) {
			$result = true;
		} else {
			$result = false;
		}
		//		echo $gets = $this->Post($post_data, $this->config['SMS_URL']);
		return $result;
	}
}