<?php

class SantongSms {

	public function __construct($config = '') {
		$config['SMS_SCODE'] = md5($config['SMS_SCODE']);
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
		if (is_array($mobile)) {
			$mobile = implode(',', $mobile);
		}
		switch ($tempid) {
			case 1:
				$cont = "{$content}（卖客疯验证码），感谢您的加入。";
				break;
			case 2:
				$cont = "亲，您的货物已于{$content}发出，如需帮助请致电4000-887-310。";
				break;
			case 3:
				$cont = "亲，您的货物已于{$content[0]}发出，物流编号{$content[1]}，由{$content[2]}承运，如需帮助请致电4000-887-310。";
			case 4:
				$cont = "亲，由于业务扩大，卖客疯正在换个大仓库~SO，7月31日到8月3日下单的亲们，我们会在8月4日统一发货！求大家理解卖姐，求大家谅解卖姐~";
		}
		$sendSmsAddress = $this->config['SMS_URL'];
		$message = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
				. "<message>"
				. "<account>"
				. $this->config['SMS_USERNAME']
				. "</account><password>"
				. $this->config['SMS_SCODE']
				. "</password>"
				. "<msgid></msgid><phones>"
				. $mobile
				. "</phones><content>"
				. $cont
				. "</content><subcode>"
				. "</subcode>"
				. "<sendtime>"
				. $time
				. "</sendtime>"
				. "</message>";
		$params = array(
			'message' => $message);

		$data = http_build_query($params);
		$context = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $data,
			)
		);
		$contents = file_get_contents($sendSmsAddress, false, stream_context_create($context));
		$xml = simplexml_load_string($contents);
		if ($xml->result == 0) {
			$result = true;
		} else {
			$result = false;
		}
		return $result;
	}

	public function balance() {
		$BalanceAddress = $this->config['SMS_BALANCE'];
		$message = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
				. "<message>"
				. "<account>"
				. $this->config['SMS_USERNAME']
				. "</account><password>"
				. $this->config['SMS_SCODE']
				. "</password>"
				. "</message>";
		$params = array(
			'message' => $message);

		$data = http_build_query($params);
		$context = array('http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $data,
		));
		$contents = file_get_contents($BalanceAddress, false, stream_context_create($context));
		$xml = simplexml_load_string($contents);
		return (int) $xml->sms->number;
	}
}
