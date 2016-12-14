<?php
namespace helperclass\helper;

/**
 * 助手类
 * @author 周伟杰
 *
 */
class Helper
{
	/**
	 * 判断当前服务器系统
	 * @return string
	 */
	public static function getOS(){
		if(PATH_SEPARATOR == ':'){
			return 'Linux';
		}else{
			return 'Windows';
		}
	}
	/**
	 * 当前微妙数
	 * @return number
	 */
	public static function microtimeFloat() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}

	/**
	 * 切割utf-8格式的字符串(一个汉字或者字符占一个字节)
	 *
	 * @author zhao jinhan
	 * @version v1.0.0
	 *
	 */
	public static function truncateUtf8String($string, $length, $etc = '...') {
		$result = '';
		$string = html_entity_decode ( trim ( strip_tags ( $string ) ), ENT_QUOTES, 'UTF-8' );
		$strlen = strlen ( $string );
		for($i = 0; (($i < $strlen) && ($length > 0)); $i ++) {
			if ($number = strpos ( str_pad ( decbin ( ord ( substr ( $string, $i, 1 ) ) ), 8, '0', STR_PAD_LEFT ), '0' )) {
				if ($length < 1.0) {
					break;
				}
				$result .= substr ( $string, $i, $number );
				$length -= 1.0;
				$i += $number - 1;
			} else {
				$result .= substr ( $string, $i, 1 );
				$length -= 0.5;
			}
		}
		$result = htmlspecialchars ( $result, ENT_QUOTES, 'UTF-8' );
		if ($i < $strlen) {
			$result .= $etc;
		}
		return $result;
	}

	/**
	 * 遍历文件夹
	 * @param string $dir
	 * @param boolean $all  true表示递归遍历
	 * @return array
	 */
	public static function scanfDir($dir='', $all = false, &$ret = array()){
		if ( false !== ($handle = opendir ( $dir ))) {
			while ( false !== ($file = readdir ( $handle )) ) {
				if (!in_array($file, array('.', '..', '.git', '.gitignore', '.svn', '.htaccess', '.buildpath','.project'))) {
					$cur_path = $dir . '/' . $file;
					if (is_dir ( $cur_path )) {
						$ret['dirs'][] =$cur_path;
						$all && self::scanfDir( $cur_path, $all, $ret);
					} else {
						$ret ['files'] [] = $cur_path;
					}
				}
			}
			closedir ( $handle );
		}
		return $ret;
	}

	/**
	 * 判断字符串是utf-8 还是gb2312
	 * @param unknown $str
	 * @param string $default
	 * @return string
	 */
	public static function isUtf8OrGb2312($str, $default = 'gb2312')
	{
	    $str = preg_replace("/[\x01-\x7F]+/", "", $str);
	    if (empty($str)) return $default;

	    $preg =  array(
	        "gb2312" => "/^([\xA1-\xF7][\xA0-\xFE])+$/", //正则判断是否是gb2312
	        "utf-8" => "/^[\x{4E00}-\x{9FA5}]+$/u",      //正则判断是否是汉字(utf8编码的条件了)，这个范围实际上已经包含了繁体中文字了
	    );

	    if ($default == 'gb2312') {
	        $option = 'utf-8';
	    } else {
	        $option = 'gb2312';
	    }

	    if (!preg_match($preg[$default], $str)) {
	        return $option;
	    }
	    $str = @iconv($default, $option, $str);

	    //不能转成 $option, 说明原来的不是 $default
	    if (empty($str)) {
	        return $option;
	    }
	    return $default;
	}

	/**
	 * utf-8和gb2312自动转化
	 * @param unknown $string
	 * @param string $outEncoding
	 * @return unknown|string
	 */
	public static function safeEncoding($string,$outEncoding = 'UTF-8')
	{
		$encoding = "UTF-8";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			if (ord ( $string {$i} ) < 128)
				continue;

			if ((ord ( $string {$i} ) & 224) == 224) {
				// 第一个字节判断通过
				$char = $string {++ $i};
				if ((ord ( $char ) & 128) == 128) {
					// 第二个字节判断通过
					$char = $string {++ $i};
					if ((ord ( $char ) & 128) == 128) {
						$encoding = "UTF-8";
						break;
					}
				}
			}
			if ((ord ( $string {$i} ) & 192) == 192) {
				// 第一个字节判断通过
				$char = $string {++ $i};
				if ((ord ( $char ) & 128) == 128) {
					// 第二个字节判断通过
					$encoding = "GB2312";
					break;
				}
			}
		}

		if (strtoupper ( $encoding ) == strtoupper ( $outEncoding ))
			return $string;
		else
			return @iconv ( $encoding, $outEncoding, $string );
	}

	/**
	 * 返回二维数组中某个键名的所有值
	 * @param input $array
	 * @param string $key
	 * @return array
	 */
	public static function arrayKeyValues($array =array(), $key='')
	{
		$ret = array();
		foreach((array)$array as $k=>$v){
			$ret[$k] = $v[$key];
		}
		return $ret;
	}

	/**
	 * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
	 * @param string $file 文件/目录
	 * @return boolean
	 */
	public static function isWriteable($file) {
		if (is_dir($file)){
			$dir = $file;
			if ($fp = @fopen("$dir/test.txt", 'w')) {
				@fclose($fp);
				@unlink("$dir/test.txt");
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		} else {
			if ($fp = @fopen($file, 'a+')) {
				@fclose($fp);
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		}

		return $writeable;
	}
	/**
	 * 格式化单位
	 */
	public static function byteFormat( $size, $dec = 2 ) {
		$a = array ( "B" , "KB" , "MB" , "GB" , "TB" , "PB" );
		$pos = 0;
		while ( $size >= 1024 ) {
			$size /= 1024;
			$pos ++;
		}
		return round( $size, $dec ) . " " . $a[$pos];
	}

	/**
	 * 下载远程图片
	 * @param string $url 图片的绝对url
	 * @param string $filepath 文件的完整路径（例如/www/images/test） ，此函数会自动根据图片url和http头信息确定图片的后缀名
	 * @param string $filename 要保存的文件名(不含扩展名)
	 * @return mixed 下载成功返回一个描述图片信息的数组，下载失败则返回false
	 */
	public static function downloadImage($url, $filepath, $filename) {
		//服务器返回的头信息
		$responseHeaders = array();
		//原始图片名
		$originalfilename = '';
		//图片的后缀名
		$ext = '';
		$ch = curl_init($url);
		//设置curl_exec返回的值包含Http头
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//设置curl_exec返回的值包含Http内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//设置抓取跳转（http 301，302）后的页面
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		//设置最多的HTTP重定向的数量
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

		//服务器返回的数据（包括http头信息和内容）
		$html = curl_exec($ch);
		//获取此次抓取的相关信息
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		if ($html !== false) {
			//分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
			$httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
			//倒数第二段是服务器最后一次response的http头
			$header = $httpArr[count($httpArr) - 2];
			//倒数第一段是服务器最后一次response的内容
			$body = $httpArr[count($httpArr) - 1];
			$header.="\r\n";

			//获取最后一次response的header信息
			preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
			if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					if (array_key_exists($i, $matches[2])) {
						$responseHeaders[$matches[1][$i]] = $matches[2][$i];
					}
				}
			}
			//获取图片后缀名
			if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
				$originalfilename = $matches[0];
				$ext = $matches[1];
			} else {
				if (array_key_exists('Content-Type', $responseHeaders)) {
					if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
						$ext = $extmatches[1];
					}
				}
			}
			//保存文件
			if (!empty($ext)) {
				//如果目录不存在，则先要创建目录
				if(!is_dir($filepath)){
					mkdir($filepath, 0777, true);
				}

				$filepath .= '/'.$filename.".$ext";
				$local_file = fopen($filepath, 'w');
				if (false !== $local_file) {
					if (false !== fwrite($local_file, $body)) {
						fclose($local_file);
						$sizeinfo = getimagesize($filepath);
						return array('filepath' => realpath($filepath), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($filepath, PATHINFO_BASENAME));
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param string $string 原文或者密文
	 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
	 * @param string $key 密钥
	 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
	 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
	 *
	 * @example
	 *
	 * $a = authcode('abc', 'ENCODE', 'key');
	 * $b = authcode($a, 'DECODE', 'key');  // $b(abc)
	 *
	 * $a = authcode('abc', 'ENCODE', 'key', 3600);
	 * $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
	 */
	public static function authCode($string, $operation = 'DECODE', $key = '', $expiry = 3600) {

		$ckey_length = 4;
		// 随机密钥长度 取值 0-32;
		// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
		// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
		// 当此值为 0 时，则不产生随机密钥


		$key = md5 ( $key ? $key : 'key' ); //这里可以填写默认key值
		$keya = md5 ( substr ( $key, 0, 16 ) );
		$keyb = md5 ( substr ( $key, 16, 16 ) );
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';

		$cryptkey = $keya . md5 ( $keya . $keyc );
		$key_length = strlen ( $cryptkey );

		$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
		$string_length = strlen ( $string );

		$result = '';
		$box = range ( 0, 255 );

		$rndkey = array ();
		for($i = 0; $i <= 255; $i ++) {
			$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
		}

		for($j = $i = 0; $i < 256; $i ++) {
			$j = ($j + $box [$i] + $rndkey [$i]) % 256;
			$tmp = $box [$i];
			$box [$i] = $box [$j];
			$box [$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i ++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box [$a]) % 256;
			$tmp = $box [$a];
			$box [$a] = $box [$j];
			$box [$j] = $tmp;
			$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
		}

		if ($operation == 'DECODE') {
			if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
				return substr ( $result, 26 );
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
		}

	}

	public static function gbkToUtf8($str){
		return iconv("GBK", "UTF-8", $str);
	}

	/**
	 * 删除文件夹及其文件夹下所有文件
	 */
	public static function deldir($dir) {
		//先删除目录下的文件：
		$dh=opendir($dir);
		while ($file=readdir($dh)) {
			if($file!="." && $file!="..") {
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					self::deldir($fullpath);
				}
			}
		}

		closedir($dh);
		//删除当前文件夹：
		if(rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * html过滤
     * @param array|object $_date
     * @return string
     */
    public static function htmlString($_date) {
        if (is_array($_date)) {
            foreach ($_date as $_key=>$_value) {
                $_string[$_key] = self::htmlString($_value);  //递归
            }
        } elseif (is_object($_date)) {
            foreach ($_date as $_key=>$_value) {
                $_string->$_key = self::htmlString($_value);  //递归
            }
        } else {
            $_string = htmlspecialchars($_date);
        }
        return $_string;
    }

    /**
     * 数据库输入过滤
     * @param string $_data
     * @return string
     */
    public static function mysqlString($_data) {
        $_data = trim($_data);
        return !GPC ? addcslashes($_data) : $_data;
    }

    /**
     * 随机字符
     * @param number $length 长度
     * @param string $type 类型
     * @param number $convert 转换大小写
     * @return string
     */
    public static function random($length=6, $type='string', $convert=0){
        $config = array(
            'number'=>'1234567890',
            'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if(!isset($config[$type])) $type = 'string';
        $string = $config[$type];

        $code = '';
        $strlen = strlen($string) -1;
        for($i = 0; $i < $length; $i++){
            $code .= $string{mt_rand(0, $strlen)};
        }
        if(!empty($convert)){
            $code = ($convert > 0)? strtoupper($code) : strtolower($code);
        }
        return $code;
    }

    /**
     * 将数组转为对象
     * @param $array
     * @return object
     */
    public static function arrayToObject($array){
        if (is_array($array)){
            $obj = new \StdClass();
            foreach ($array as $key => $val){
                $obj->$key = $val;
            }
        }else{
            $obj = $array;
        }
        return $obj;
    }

    /**
     * 将数组转为对象
     * @param $object
     * @return array
     */
    public static function objectToArray($object){
        $array = array();
        if (is_object($object)){
            foreach ($object as $key => $value){
                $array[$key] = $value;
            }
        }else{
            $array = $object;
        }
        return $array;
    }

    /**
     * 删除更新过程中没有变化的Input参数
     * @param array|object $data 原数据
     * @param array $input
     * @return array 变动后的input
     */
    public static function removeNoChangeInput($data, $input){
        if(empty($data) || empty($input)){
            return $input;
        }
        if(!is_array($input)){
            return $input;
        }
        if( is_array($data) ){
            foreach ($input as $k=>$v) {
                if(isset($data[$k]) && $data[$k] === $v){
                    unset($input[$k]);
                }
            }
        }
        elseif(is_object($data)){
            foreach ($input as $k=>$v) {
                if(isset($data->$k) && $data->$k === $v){
                    unset($input[$k]);
                }
            }
        }
        return $input;
    }

    /**
     * 获取毫秒级的时间戳
     * @return float
     */
    public static function getMillisecond(){
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}
