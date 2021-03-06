<?php

/**
 * 是否正在访问后台
 *
 * @param  string  $adminName 后台模块的路由名称
 * @return boolean
 */
function is_access_admin($adminName = 'admin')
{
    $host = explode('.', $_SERVER['HTTP_HOST'])[0];
    if ($host === $adminName) {
        return true;
    } elseif (($uri = $_SERVER['REQUEST_URI']) !== '/') {
        $params = preg_split('#[/\?]#', $uri);
        $id = $params[1];
        return $id === $adminName;
    } else {
        return false;
    }
}

/**
 * 过滤启动项配置
 * 
 * @param  array $bootstrap 能直接通过路由加载的模块
 * @param  array $extra     必须加载的模块
 * @return array            筛选过后需要加载的模块
 */
function bootstrap_filter($bootstrap = [], $extra = [])
{
    $isCli = PHP_SAPI === 'cli';
    if (!$isCli) {
        $host = explode('.', $_SERVER['HTTP_HOST'])[0];
        if (in_array($host, $bootstrap)) {
            $bootstrap = [$host];
        } elseif (($uri = $_SERVER['REQUEST_URI']) !== '/') {
            $params = preg_split('#[/\?]#', $uri);
            $id = $params[1];
            $bootstrap = array_filter($bootstrap, function ($value) use ($id) {
                return $value === $id;
            });
        } else {
            $bootstrap = [];
        }
    } else {
        $bootstrap = [];
    }
    if (!$isCli) {
        $bootstrap = array_unique(array_merge($bootstrap, $extra));
    }

    return $bootstrap;
}

/**
 * 将xml转为array
 * @param string $xml
 */
function fromXml($xml)
{ 
    if(!$xml){
      throw new Exception("xml数据异常！");
    }
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);    
}

// 文件记录方式记录log
function file_log($data)
{
    file_put_contents('file_log.txt', date('Y-m-d H:i:s') . ':' . var_export($data, true) . "\r\n", FILE_APPEND);
}

function sendsms($tel, $code)
{
    if (!preg_match('/^1[34578]\d{9}$/', $tel)) {
      return ['code' => 1, 'info' => '您输入的不是一个手机号！'];
    }
    $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

//    if (session('ip_' . $ip)) {
//      return ['code' => 1, 'info' => '短信已发送请在60秒后再次点击发送！'];
//    }
    session('ip_' . $ip, $tel, 60);

    $statusStr = array(
    "0" => "短信发送成功",
    "-1" => "参数不全",
    "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
    "30" => "密码错误",
    "40" => "账号不存在",
    "41" => "余额不足",
    "42" => "帐户已过期",
    "43" => "IP地址限制",
    "50" => "内容含有敏感词"
    );
        $smsapi = "http://utf8.api.smschinese.cn/"; //短信网关
		$user = 'lanniaoguoji'; //短信平台帐号
		$content = '您好，您的验证码是' .  $code; //要发送的短信内容
		$sendurl = $smsapi . "?Uid=" . $user . "&Key=d41d8cd98f00b204e980&smsMob=" . $tel . "&smsText=" . $content;
		$res = file_get_contents($sendurl);
		if ($res > 0) {
            session('ip_' . $ip, $tel, 60);
            session('verifyCode', $code, 1800);
            session('registerMobile', $tel, 1800);
            return success('发送成功');
        } else {
            return error('发送失败');
        }
    }


    function sendcode($tel,$code)
    {
        if (!preg_match('/^1[34578]\d{9}$/', $tel)) {
            return ['code' => 1, 'info' => '您输入的不是一个手机号！'];
        }
        $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        session('ip_' . $ip, $tel, 60);
      $res =  curl_request('http://api.1cloudsp.com/api/v2/single_send',[
            'accesskey'=>'YzEnar5M55RtMZB3',
            'secret'=>'87rJ6G8SkhThrzPlct8S1uiXVyaY0Vfn',
            'sign'=>133392,
            'templateId'=>25950,
            'mobile'=>$tel,
            'content'=>$code
        ]);
     $res = json_decode($res,1);
     if (isset($res['msg']) && $res['msg'] == 'SUCCESS'){
         session('ip_' . $ip, $tel, 60);
         session('verifyCode', $code, 1800);
         session('registerMobile', $tel, 1800);
         return ['code' => 2, 'info' => '发送成功！'];
     }else{
         return ['code' => 1, 'info' => '发送失败！'];
     }
    }

    function clean($str)
    {
        $str=trim($str);
        $str=strip_tags($str);
        $str=stripslashes($str);
        $str=addslashes($str);
        $str=rawurldecode($str);
        $str=quotemeta($str);
        $str=htmlspecialchars($str);
        //去除特殊字符
        $str=preg_replace("/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/", "" , $str);
        $str=preg_replace("/\s/", "", $str);//去除空格、换行符、制表符
        return $str;
    }

    function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
    /**
     * 调试专用，可以传入任意多的变量进行打印查看
     */
    function tes()
    {
        $isCli = PHP_SAPI === 'cli';
        if (!$isCli && !in_array('Content-type:text/html;charset=utf-8', headers_list())) {
            header('Content-type:text/html;charset=utf-8');
        }

        if (in_array(debug_backtrace()[2]['function'], ['dump'])) {
            $printFunc = 'var_dump';
        } else {
            $printFunc = 'print_r';
        }

        foreach (func_get_args() as $msg) {
            if ($isCli) {
                $printFunc($msg);
                echo PHP_EOL;
            } else {
                echo '<xmp>';
                $printFunc($msg);
                echo '</xmp>';
            }
        }
    }

/**
 * @see tes()
 */
function test()
{
    call_user_func_array('tes', func_get_args());
    exit;
}

/**
 * @see tes()
 */
function dump()
{
    call_user_func_array('tes', func_get_args());
    exit;
}

/**
 * 请求数据
 */
function httpRequest($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 获取汇率
 *
 * @param $currency
 *
 * @return int|mixed
 */
function getExchangeRate($currency)
{
    // 交易汇率
    switch ($currency) {
        case 1: // 人民币
        default;
            $exchangeRate = 1;
            break;
        case 2: // 美元
            $exchangeRate = config('USD');
            break;
        case 3: // 港元
            $exchangeRate = config('HKD');
            break;
        case 4: // 欧元
            $exchangeRate = config('EURO');
            break;
    }
    if (empty($exchangeRate) || $exchangeRate < 0) {
        $exchangeRate = 1;
    }

    return $exchangeRate;
}

/**
 * 币种符号
 *
 * @param $currency
 *
 * @return int|mixed
 */
function getCurrencySymbol($currency)
{
    switch ($currency) {
        case 1: // 人民币
        default;
            $symbol = '￥';
            break;
        case 2: // 美元
            $symbol = '$';
            break;
        case 3: // 港元
            $symbol = 'HK$';
            break;
        case 4: // 欧元
            $symbol = '€';
            break;
    }
    if (empty($symbol) || $symbol < 0) {
        $symbol = '￥';
    }

    return $symbol;
}

/*
 * 检测是否手机客户端请求
 * return bool
 */
if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $tel_agents = [
            '240x320',
            'acer',
            'acoon',
            'acs-',
            'abacho',
            'ahong',
            'airness',
            'alcatel',
            'amoi',
            'android',
            'anywhereyougo.com',
            'applewebkit/525',
            'applewebkit/532',
            'asus',
            'audio',
            'au-mic',
            'avantogo',
            'becker',
            'benq',
            'bilbo',
            'bird',
            'blackberry',
            'blazer',
            'bleu',
            'cdm-',
            'compal',
            'coolpad',
            'danger',
            'dbtel',
            'dopod',
            'elaine',
            'eric',
            'etouch',
            'fly ',
            'fly_',
            'fly-',
            'go.web',
            'goodaccess',
            'gradiente',
            'grundig',
            'haier',
            'hedy',
            'hitachi',
            'htc',
            'huawei',
            'hutchison',
            'inno',
            'ipad',
            'ipaq',
            'ipod',
            'jbrowser',
            'kddi',
            'kgt',
            'kwc',
            'lenovo',
            'lg ',
            'lg2',
            'lg3',
            'lg4',
            'lg5',
            'lg7',
            'lg8',
            'lg9',
            'lg-',
            'lge-',
            'lge9',
            'longcos',
            'maemo',
            'mercator',
            'meridian',
            'micromax',
            'midp',
            'mini',
            'mitsu',
            'mmm',
            'mmp',
            'mobi',
            'mot-',
            'moto',
            'nec-',
            'netfront',
            'newgen',
            'nexian',
            'nf-browser',
            'nintendo',
            'nitro',
            'nokia',
            'nook',
            'novarra',
            'obigo',
            'palm',
            'panasonic',
            'pantech',
            'philips',
            'phone',
            'pg-',
            'playstation',
            'pocket',
            'pt-',
            'qc-',
            'qtek',
            'rover',
            'sagem',
            'sama',
            'samu',
            'sanyo',
            'sch-',
            'scooter',
            'sec-',
            'sendo',
            'sgh-',
            'sharp',
            'siemens',
            'sie-',
            'softbank',
            'sony',
            'spice',
            'sprint',
            'spv',
            'symbian',
            'tablet',
            'talkabout',
            'tcl-',
            'teleca',
            'telit',
            'tianyu',
            'tim-',
            'toshiba',
            'tsm',
            'up.browser',
            'utec',
            'utstar',
            'verykool',
            'virgin',
            'vk-',
            'voda',
            'voxtel',
            'vx',
            'wap',
            'wellco',
            'wig browser',
            'wii',
            'windows ce',
            'wireless',
            'xda',
            'xde',
            'zte',
        ];
        $is_mobile = false;
        foreach ($tel_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }

        return $is_mobile;
    }
}
