<?php

/**
 * 短信类
 *
 * @author Gui Gui <sd2536888@163.com>
 */
class Sms {

	public $headle;
	public $config = array(
		'SMS_URL' => '',
		'SMS_USERNAME' => '', //用户
		'SMS_SCODE' => '', //密码
	);

	public function __construct() {
		$config = require '../conf/sms.inc.php';
		$class = $config['SMS_TYPE'] . 'Sms';
		import("COM.Sms.{$class}");
		$this->headle = new $class($config[$class]);
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
		$result = $this->headle->send($mobile, $content, $tempid, $time);
		return $result;
	}

	/**
	 * 查询余额
	 * @return array array(0,数字) 提交成功，格式：array(返回值,短信余额条数)
	 */
	public function balance() {
		$result = $this->headle->balance();
		return $result;
	}

	static public function curl($data, $url, $is_post = 1) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($is_post) {
			curl_setopt($ch, CURLOPT_POST, 1);
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$reslut = curl_exec($ch);
		return $reslut;
	}

}
