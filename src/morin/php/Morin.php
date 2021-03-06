<?php
namespace morin\php;

class Morin {
	// 带refer的跳转
	static public function href($url) {
		header("Content-Type: text/html; charset=utf-8");
		echo "<script>window.location.href='" . $url . "'</script>";exit;
	}

	// 返回前端json格式
	static public function json($code = 200, $msg = 'success', $data = null) {
		header('content-type: application/json;charset=utf-8');
		echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);exit;
	}

	/**
	 * 按天向指定目录写日志
	 * @param [string] $name 文件名
	 * @param [string] $msg 内容
	 */
	static public function logs($name = "filename", $msg = "default") {
		$logdir = './logs/';
		if (!is_dir($logdir)) {
			mkdir('./logs', 0777);
			chmod('./logs', 0777);
		}
		if (!is_writeable($logdir)) {
			exit('error: Log directory "' . $logdir . '" Do not exist or do not write permission!');
		}
		$filename = $logdir . $name . '_' . date('Ymd') . '.log';
		if (file_exists($filename) && !is_writeable($filename)) {
			exit('error:Log file "' . $filename . '" No write permission!');
		}
		$msg = '[' . date('H:i:s') . ']' . $msg . "\n";
		error_log($msg, 3, $filename);
	}

	// 生成唯一key
	static public function gukey() {
		$guid = strtoupper(md5(uniqid(mt_srand(), true)));
		return $guid;
	}

	// 生成唯一标识符
	static public function guid($trim = true) {
		// Windows
		if (function_exists('com_create_guid') === true) {
			if ($trim === true) {
				return trim(com_create_guid(), '{}');
			} else {
				return com_create_guid();
			}
		}

		// Fallback (PHP 4.2+)
		mt_srand((double) microtime() * 10000);
		$charid = strtolower(md5(uniqid(rand(), true)));
		$hyphen = chr(45); // "-"
		$lbrace = $trim ? "" : chr(123); // "{"
		$rbrace = $trim ? "" : chr(125); // "}"
		$guidv4 = $lbrace .
		substr($charid, 0, 8) . $hyphen .
		substr($charid, 8, 4) . $hyphen .
		substr($charid, 12, 4) . $hyphen .
		substr($charid, 16, 4) . $hyphen .
		substr($charid, 20, 12) .
			$rbrace;
		return $guidv4;
	}

	// 订单号
	static public function order_num() {
		$microtime = explode(' ', microtime())[0] * 1000000;
		return date('YmdHis') . str_pad($microtime, 8, 0, STR_PAD_LEFT);
	}

	/**
	 * 设置当前请求绑定的对象实例
	 * @access public
	 * @param [string] $url 请求地址
	 * @param [bool]  $https 是否https协议
	 * @param [string] $method 请求方式
	 * @param [array] $data 请求数据
	 * @return [string]
	 */
	static public function curl($url = 'baidu.com', $method = 'get', $data = null, $https = true) {
		//初始化curl
		$ch = curl_init($url);
		//字符串不直接输出，进行一个变量的存储
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//https请求
		if ($https === true) {
			//确保https请求能够请求成功
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		//post请求
		if ($method == 'post') {
			// curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		//发送请求
		$str = curl_exec($ch);
		$aStatus = curl_getinfo($ch);
		//关闭连接
		curl_close($ch);
		if (intval($aStatus["http_code"]) == 200) {
			// return json_decode($str);
			return $str;
		} else {
			return false;
		}
	}

	/**
	 * 距离当前时间差
	 * @access public
	 * @param [timestamp] $time 时间戳
	 * @return [string]
	 */
	static public function passed_time($time = 0) {
		$t = time() - $time;
		if ($t <= 0) {
			return false;
		} else {
			$f = array(
				'31536000' => '年',
				'2592000' => '个月',
				'604800' => '星期',
				'86400' => '天',
				'3600' => '小时',
				'60' => '分钟',
				'1' => '秒',
			);
			foreach ($f as $k => $v) {
				if (0 != $c = floor($t / (int) $k)) {
					return $c . $v . '前';
				}
			}
		}
	}

	// 创建随机字符串
	static public function noncestr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	/**
	 *
	 * @param string $string 需要加密的字符串
	 * @param string $key 密钥
	 * @return string
	 */
	static public function encrypt($string = "", $key = "") {
		// openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
		$data = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
		$data = strtolower(bin2hex($data));
		return $data;
	}

	/**
	 * @param string $string 需要解密的字符串
	 * @param string $key 密钥
	 * @return string
	 */
	static public function decrypt($string = "fc041974581f3bfede58b726b4f88941", $key = "") {
		$decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
		return $decrypted;
	}

	// 递归树
	static public function build_tree($data = [], $pid = 0) {
		$treenodes = [];
		foreach ($data as $k => $v) {
			if ($v['pid'] == $pid) {
				$v['children'] = build_tree($data, $v['id']);
				$treenodes[] = $v;
			}
		}
		return $treenodes;
	}

	/**
	 * 数字序列转字母序列,大写字母ascii码从65~106
	 * @param $int
	 * @param int $start
	 * @return string|bool
	 */
	static public function int2word($int = 0) {
		if (!is_int($int) || $int <= 0) {
			return false;
		}

		$str = '';
		if ($int > 26) {
			$str .= int_to_chr((int) floor($int / 26));
		}
		if ($int % 26 == 0) {
			return $str . chr(26 + 64);
		}
		return $str . chr($int % 26 + 64);
	}

	/**
	 * 字母序列转数字序列
	 * @param $char
	 * @return int|bool
	 */
	static public function word2int($char = "") {
		//检测字符串是否全字母
		$regex = '/^[a-zA-Z]+$/i';

		if (!preg_match($regex, $char)) {
			return false;
		}

		$int = 0;
		$char = strtoupper($char);
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$len = strlen($char);
		for ($i = 0; $i < $len; $i++) {
			$index = array_search($char[$i], $array);
			$int += ($index + 1) * pow(26, $len - $i - 1);
		}
		return $int;
	}

	// 时间戳转中文星期
	static public function week2cn($timestamp = 0) {
		$timestamp = empty($timestamp) ? time() : $timestamp;
		$week = ['一', '二', '三', '四', '五', '六', '日'];
		return $week[date('N', $timestamp) - 1];
	}

	// 获取泛域名
	static function fan_host() {
		$R = request();
		$domain = parse_url($R->host());
		if (isset($domain['path'])) {
			$arr = explode('.', $domain['path']);
		} else {
			$arr = explode('.', $domain['host']);
		}
		$index = count($arr);
		if ($index > 1) {
			$servername = '.' . $arr[$index - 2] . '.' . $arr[$index - 1];
		} else {
			$servername = 'localhost';
		}
		return $servername;
	}

	static function http_status_code($code) {
		$http = array(
			100 => "HTTP/1.1 100 Continue",
			101 => "HTTP/1.1 101 Switching Protocols",
			200 => "HTTP/1.1 200 OK",
			201 => "HTTP/1.1 201 Created",
			202 => "HTTP/1.1 202 Accepted",
			203 => "HTTP/1.1 203 Non-Authoritative Information",
			204 => "HTTP/1.1 204 No Content",
			205 => "HTTP/1.1 205 Reset Content",
			206 => "HTTP/1.1 206 Partial Content",
			300 => "HTTP/1.1 300 Multiple Choices",
			301 => "HTTP/1.1 301 Moved Permanently",
			302 => "HTTP/1.1 302 Found",
			303 => "HTTP/1.1 303 See Other",
			304 => "HTTP/1.1 304 Not Modified",
			305 => "HTTP/1.1 305 Use Proxy",
			307 => "HTTP/1.1 307 Temporary Redirect",
			400 => "HTTP/1.1 400 Bad Request",
			401 => "HTTP/1.1 401 Unauthorized",
			402 => "HTTP/1.1 402 Payment Required",
			403 => "HTTP/1.1 403 Forbidden",
			404 => "HTTP/1.1 404 Not Found",
			405 => "HTTP/1.1 405 Method Not Allowed",
			406 => "HTTP/1.1 406 Not Acceptable",
			407 => "HTTP/1.1 407 Proxy Authentication Required",
			408 => "HTTP/1.1 408 Request Time-out",
			409 => "HTTP/1.1 409 Conflict",
			410 => "HTTP/1.1 410 Gone",
			411 => "HTTP/1.1 411 Length Required",
			412 => "HTTP/1.1 412 Precondition Failed",
			413 => "HTTP/1.1 413 Request Entity Too Large",
			414 => "HTTP/1.1 414 Request-URI Too Large",
			415 => "HTTP/1.1 415 Unsupported Media Type",
			416 => "HTTP/1.1 416 Requested range not satisfiable",
			417 => "HTTP/1.1 417 Expectation Failed",
			500 => "HTTP/1.1 500 Internal Server Error",
			501 => "HTTP/1.1 501 Not Implemented",
			502 => "HTTP/1.1 502 Bad Gateway",
			503 => "HTTP/1.1 503 Service Unavailable",
			504 => "HTTP/1.1 504 Gateway Time-out",
		);
		header($http[$code]);
	}
}