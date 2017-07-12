<?php

namespace vendor\Sms;

/**
 * 短信类
 *
 * @author test
 */
class MeishengSms {
	
	public function __construct($config = '') {
		vendor("Sms.HttpClient", '', '.class.php');
		$this->config = $config;
	}

	/**
	 * 发送短信
	 * @param int|str $mobile 手机号码（最多100个），多个用英文逗号(,)隔开
	 * @param str $content 短信内容（最多300个汉字），特殊字符处理：%请使用中文％代替；
	 *						 如果使用模板短信发送，此参数用来传递模板短信的变量和值。
	 * @param int $tempid 模板号
	 * @param int $time 发送时间,格式为:年年年年月月日日时时分分秒秒,例如:20090504111010.为空时，为即时发送短信
	 * @return array array(0,数字,数字) 提交成功，格式：array(返回值,提交计费条数,提交成功号码数)
	 */
	public function send($mobile, $content, $tempid = 1, $time = null) {
		$temp = array(
			'1' => 'MB-201401', //@1@（卖客疯验证码），欢迎您的加入。
			'2' => 'MB-201402', //亲，您的货物已于@1@发出，如需帮助请致电4000-887-310。
			'3' => 'MB-201403', //亲，您的货物已于@1@发出，物流编号@2@，由@3@承运，如需帮助请致电4000-887-310。
			'4' => 'MB-201405', //您好！您退回的订单尾号为@1@的商品我们已收到！我们会在5个工作日左右退款到您付款账户！如有疑问，请联系400-088-7310
			'5' => 'MB-201406', //您好！您退回的订单尾号为@1@的商品我们已收到！我们会尽快为您发送新的商品！如有疑问，请联系400-088-7310
			'6' => 'MB-201407', //亲爱的@1@童鞋，您的手机号对应获得的代码是 @2@ 记得关注相应时段公布获奖名单。
		);
		$params = array(
			'username' => $this->config['SMS_USERNAME'],
			'scode' => $this->config['SMS_SCODE'],
			'mobile' => $mobile,
			'tempid' => $temp[$tempid],
		);
		switch ($tempid) {
			case 1:
			case 2:
			case 4:
			case 5:
				$params['content'] = '@1@=' . $content;
				break;
			case 3:
			case 6:
				foreach ($content as $key => $value) {
					$cont[] = '@' . ($key+1). '@=' . mb_convert_encoding($value, 'GB2312', 'UTF-8');
				}
				$params['content'] = implode(',', $cont);
				break;
		}
		//发送时间
		if (!empty($time)) {
			$params['sendtime'] = date('YmdHis', $time);
		}
		$pageContents = HttpClient::quickPost($this->config['SMS_URL'].'sendsms.jsp', $params);
		$array = explode('#', $pageContents);
		foreach ($array as $key => $value) {
			$array[$key] = trim($value);
		}
		if ($array[0] == 0) {
			$result = true;
		} else {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * 查询余额
	 * @return array array(0,数字) 提交成功，格式：array(返回值,短信余额条数)
	 */
	public function balance(){
		$params = array(
			'username' => $this->config['SMS_USERNAME'],
			'scode' => $this->config['SMS_SCODE'],
		);
		$pageContents = HttpClient::quickPost($this->config['SMS_URL'].'balance.jsp', $params);
		$array = explode('#', $pageContents);
		foreach ($array as $key => $value) {
			$array[$key] = trim($value);
		}
		return $array[1];
	}

}
