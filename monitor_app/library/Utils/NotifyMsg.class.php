<?php
class NotifyMsg{
    
    const SERVER_ERROR = 'monitor.admin.error!';
    /*
        $name : 脚本名字
        $content:报警内容
    */
    public static function notify($name, $content){
        
        $base_url = 'http://monitor.test.com/alert?';
        $params = array(
            'name' => $name,
            'msg' => $content
        );
        $url = $base_url.http_build_query($params);
        $ret = $this->http_get($url, FALSE);
        $ret = json_decode($ret);
        if(is_array($ret) && $ret['code'] != 0){
            log_message(sprintf('request log failed, name:%s, msg:%s'.PHP_EOL, $name, $content), LOG_ERR);
        }
    }

    public static function notifyError($msg , $hostName = ''){
        if (empty($hostName))
        {
            $hostName = isset($_SERVER['HTTP_HOST']) ? rtrim($_SERVER['HTTP_HOST'],'/') : '';
            if (empty($hostName))
            {
                if (isset($argv) && isset($argv[0]))
                {
                    $hostName = $argv[0];
                }
            }
        }
        $msg = '<b>'.$hostName.':</b>'.$msg;
        return self::notify(self::SERVER_ERROR , $msg);
    }


    public function http_get($url, $json = true, $log = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // curl_setopt($ch, CURLOPT_SSLVERSION, 3);

        $output = curl_exec($ch);
        if ($log) {
             log_message('send data:[' . $url . '] recv data:[' . $output . ']', LOG_INFO);
        }
        $curl_info = curl_getinfo($ch);
        if (FALSE == $output) {
            return FALSE;
        }

        //返回状态
        if (!in_array($curl_info['http_code'], array(200))) {
            return FALSE;
        }

        if (!empty($curl_info['content_type']) && preg_match('#charset=([^;]+)#i', $curl_info['content_type'], $matches)) {
            $encoding = strtoupper($matches[1]);
            if ($encoding != 'UTF8' && $encoding != 'UTF-8')
            {
                $output = iconv($encoding, 'UTF-8', $output);
            }
        }
        curl_close($ch);
        return $json ? json_decode($output, TRUE) : $output;
    }
}

?>