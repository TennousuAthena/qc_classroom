<?php
/**
 * 从TCP数据流中解析HTTP协议
 * Created by Lane
 * User: lane
 * Date: 16/3/26
 * Time: 下午10:27
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\ApplicationProtocol;

use MeepoPS\Core\TransportProtocol\TransportProtocolInterface;
use MeepoPS\Core\Log;

class Http implements ApplicationProtocolInterface
{
    //每一个链接对应一个实例, 每一个示例都是一个HTTP协议类
    private static $_httpInstance = null;
    //HTTP 头
    private $_httpHeader = array();

    /**
     * 将输入的内容(包)进行检测.返回包的长度(可以为0,如果为0则等待下个数据包),如果失败返回false并关闭参数中的链接.
     * @param string $data 数据包
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return int
     */
    public static function input($data, TransportProtocolInterface $connect)
    {
        $position = strpos($data, "\r\n\r\n");
        //如果数据是\r\n\r\n开头,或者如果数据没有找到\r\n\r\n,表示数据未完.则不处理
        if (!$position) {
            return 0;
        }
        //将数据按照\r\n\r\n分割为两部分.第一部分是http头,第二部分是http body
        $http = explode("\r\n\r\n", $data, 2);
        //如果长度大于所能接收的Tcp所限制的最大数据量,则抛弃数据部分, 只发请求
        if(strlen($data) > MEEPO_PS_TCP_CONNECT_READ_MAX_PACKET_SIZE){
            $http[1] = '';
            $http[0] = preg_replace("/\r\nContent-Length: ?(\d+)/", "\r\nContent-Length: 0", $http[0]);
            Log::write('Http protocol: The received data size exceeds the maximum set of size', 'WARNING');
        }
        //非POST请求
        if (strpos($http[0], "POST") !== 0) {
            //返回头长度+4(\r\n\r\n)
            return strlen($http[0]) + 4;
        }
        //POST请求
        if (!preg_match("/\r\nContent-Length: ?(\d+)/", $http[0], $match)) {
            return 0;
        }
        //POST数据是否全部传输完毕。这里的验证是因为,在上传文件的时候, 可能HTTP header过来了, 但是HTTP body没过来
        if(strlen($http[1]) != $match[1]){
            return 0;
        }
        //返回数据长度+头长度+4(\r\n\r\n)
        return strlen($http[0]) + $match[1] + 4;
    }

    /**
     * 将数据封装为HTTP协议数据
     * @param string $data 数据包
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return string
     */
    public static function encode($data, TransportProtocolInterface $connect)
    {
        //状态码
        $header = isset(self::$_httpInstance->_httpHeader['Http-Code']) ? self::$_httpInstance->_httpHeader['Http-Code'] : 'HTTP/1.1 200 OK';
        $header .= "\r\n";
        //Connection
        $header .= isset(self::$_httpInstance->_httpHeader['Connection']) ? self::$_httpInstance->_httpHeader['Connection'] : 'Connection: keep-alive';
        $header .= "\r\n";
        //Content-Type
        $header .= isset(self::$_httpInstance->_httpHeader['Content-Type']) ? self::$_httpInstance->_httpHeader['Content-Type'] : 'Content-Type: text/html; charset=utf-8';
        $header .= "\r\n";
        unset(self::$_httpInstance->_httpHeader['Http-Code']);
        unset(self::$_httpInstance->_httpHeader['Connection']);
        unset(self::$_httpInstance->_httpHeader['Content-Type']);
        //其他部分
        foreach (self::$_httpInstance->_httpHeader as $name => $value) {
            if ($name === 'Set-Cookie' && is_array($value)) {
                foreach ($value as $v) {
                    $header .= $v . "\r\n";
                }
                continue;
            }
            $header .= $value . "\r\n";
        }
        //完善HTTP头的固定信息
        $header .= 'Server: MeepoPS/' . MEEPO_PS_VERSION . "\r\n";
        $header .= 'X-Powered-By:: PHP/' . PHP_VERSION . "\r\n";
        $header .= 'Content-Length: ' . strlen($data) . "\r\n\r\n";
        self::$_httpInstance = null;
        //返回一个完整的数据包(头 + 数据)
        return $header . $data;
    }

    /**
     * 将数据包根据HTTP协议解码
     * @param string $data 待解码的数据
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return array
     */
    public static function decode($data, TransportProtocolInterface $connect)
    {
        //实例化链接
        self::$_httpInstance = new Http();
        //将超全局变量设为空.
        $_POST = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = $GLOBALS['HTTP_RAW_POST_DATA'] = array();
        $_SERVER = array();
        //解析HTTP头
        $http = explode("\r\n\r\n", $data, 2);
        $headerList = explode("\r\n", $http[0]);

        // ---------- 填充$_SERVER ----------
        //HTTP协议开头第一行
        list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ', $headerList[0]);
        //客户端IP和端口
        $clientAddress = $connect->getClientAddress();
        $_SERVER['REMOTE_ADDR'] = $clientAddress[0];
        $_SERVER['REMOTE_PORT'] = $clientAddress[1];
        $_SERVER['SERVER_SOFTWARE'] = 'MeepoPS/' . MEEPO_PS_VERSION . '( ' . PHP_OS . ' ) PHP/' . PHP_VERSION;
        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        unset($headerList[0]);
        //循环剩下的HTTP头信息
        foreach ($headerList as $header) {
            if (empty($header)) {
                continue;
            }
            //将一条头分割为名字和值
            $header = explode(':', $header, 2);
            $name = trim(strtolower($header[0]));
            $value = trim($header[1]);
            switch ($name) {
                case 'host':
                    $_SERVER['HTTP_HOST'] = $value;
                    $value = explode(':', $value);
                    $_SERVER['SERVER_NAME'] = $value[0];
                    if (isset($value[1])) {
                        $_SERVER['SERVER_PORT'] = $value[1];
                    }
                    break;
                case 'user-agent':
                    $_SERVER['HTTP_USER_AGENT'] = $value;
                    break;
                case 'accept':
                    $_SERVER['HTTP_ACCEPT'] = $value;
                    break;
                case 'content-length':
                    $_SERVER['Content-Length'] = strlen($data);
                    break;
                case 'content-type':
                    if (!preg_match('/boundary="?(\S+)"?/', $value, $match)) {
                        $_SERVER['CONTENT_TYPE'] = $value;
                    } else {
                        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
                        $postBoundary = $match[1];
                    }
                    break;
                case 'accept-charset':
                    $_SERVER['HTTP_ACCEPT_CHARSET'] = $value;
                    break;
                case 'accept-language':
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $value;
                    break;
                case 'accept-encoding':
                    $_SERVER['HTTP_ACCEPT_ENCODING'] = $value;
                    break;
                case 'connection':
                    $_SERVER['HTTP_CONNECTION'] = $value;
                    break;
                case 'referer':
                    $_SERVER['HTTP_REFERER'] = $value;
                    break;
                case 'if-modified-since':
                    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $value;
                    break;
                case 'if-none-match':
                    $_SERVER['HTTP_IF_NONE_MATCH'] = $value;
                    break;
                case 'cookie':
                    $_SERVER['HTTP_COOKIE'] = $value;
                    $cookieList = explode(';', $value);
                    foreach($cookieList as $cookie){
                        $cookie = explode('=', $cookie);
                        if(count($cookie) === 2){
                            $_COOKIE[trim($cookie[0])] = trim($cookie[1]);
                        }
                    }
                    unset($cookieList, $cookie);
                    break;
            }
        }
        unset($name, $value, $header, $headerList);

        //GET
        parse_str($_SERVER['QUERY_STRING'], $_GET);

        //POST请求
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //multipart/form-data 类型
            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'multipart/form-data' && !empty($postBoundary)) {
                self::_parseMultipartForm($http[1], $postBoundary);
            //application/x-www-form-urlencoded 类型 或者 text/plain
            } else {
                parse_str($http[1], $_POST);
                $GLOBALS['HTTP_RAW_POST_DATA'] = $http[1];
            }
        }
        unset($http);

        //REQUEST
        $_REQUEST = array_merge($_GET, $_POST);

        return true;
    }

    /**
     * 设置http头
     * @param string $string 头字符串
     * @param bool $replace 是否用后面的头替换前面相同类型的头.即相同的多个头存在时,后来的会覆盖先来的.
     * @param int $httpResponseCode 头字符串
     * @return bool
     */
    public static function setHeader($string, $replace = true, $httpResponseCode = 0)
    {
        if (PHP_SAPI !== 'cli') {
            $httpResponseCode ? header($string, $replace, $httpResponseCode) : header($string, $replace);
            return true;
        }
        //第一种以“HTTP/”开头的 (case is not significant)，将会被用来计算出将要发送的HTTP状态码。
        //第二种特殊情况是“Location:”的头信息。它不仅把报文发送给浏览器，而且还将返回给浏览器一个 REDIRECT（302）的状态码，除非状态码已经事先被设置为了201或者3xx。
        if (strpos($string, 'HTTP') === 0) {
            $key = 'Http-Code';
        } else {
            $headerArray = explode(':', $string, 2);
            $key = $headerArray[0];
            if (empty($key)) {
                return false;
            }
            //如果是302跳转
            if (strtolower($key) === 'location' && !$httpResponseCode) {
                $httpResponseCode = 302;
            }
        }
        $httpCodeList = self::getHttpCode();
        if (isset($httpCodeList[$httpResponseCode])) {
            self::$_httpInstance->_httpHeader['Http-Code'] = 'HTTP/1.1 ' . $httpResponseCode . ' ' . $httpCodeList[$httpResponseCode];
            if ($key === 'Http-Code') {
                return true;
            }
        }
        $key === 'Set-Cookie' ? (self::$_httpInstance->_httpHeader[$key][] = $string) : (self::$_httpInstance->_httpHeader[$key] = $string);
        return true;
    }

    /**
     * 删除header()设置的HTTP头信息
     * @param string $name 删除指定的头信息
     */
    public static function delHttpHeader($name)
    {
        if (PHP_SAPI != 'cli') {
            header_remove();
        } else {
            unset(self::$_httpInstance->_httpHeader[$name]);
        }

    }

    /**
     * 设置Cookie
     * 参数意义请参考setCookie()
     * @param string $name
     * @param string $value
     * @param integer $maxage
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return bool
     */
    public static function setCookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        if (PHP_SAPI != 'cli') {
            return \setcookie($name, $value, $maxage, $path, $domain, $secure, $httpOnly);
        }
        return self::setHeader(
            'Set-Cookie: ' . $name . '=' . urlencode($value)
            . (empty($domain) ? '' : '; Domain=' . $domain)
            . (empty($maxage) ? '' : '; Max-Age=' . $maxage)
            . (empty($path) ? '' : '; Path=' . $path)
            . (empty($secure) ? '' : '; Secure')
            . (empty($httpOnly) ? '' : '; HttpOnly'), false);
    }

    /**
     * 解析multipart/form-data类型的form表单
     * @param $postData string HTTP数据部分
     * @param $postBoundary string HTTP的数据部分中, 每个字段的分割
     * @return void
     */
    private static function _parseMultipartForm($postData, $postBoundary){
        $postBoundary = '--' . $postBoundary;
        //去掉结尾的boundary和--\r\n
        $postData = rtrim($postData, "{$postBoundary}--\r\n");
        //去掉开头的boundary和\r\n
        $postData = ltrim($postData, "{$postBoundary}\r\n");
        $postDataMapList = explode("\r\n{$postBoundary}\r\n", $postData);
        //提取每个字段的名称和值
        $postUrlEncode = '';
        foreach ($postDataMapList as $postDataMap) {
            //分割为描述信息和数据
            $postDataMap = explode("\r\n\r\n", $postDataMap, 2);
            if(preg_match('/; name="(.*)"/Ui', $postDataMap[0], $name)) {
                //如果不是上传文件
                if (!preg_match('/; filename="(.*)"/', $postDataMap[0], $filename)) {
                    $postUrlEncode .= urlencode($name[1]) . '=' . urlencode($postDataMap[1]) . '&';
                //如果是上传文件
                } else {
                    //填充$_FILE
                    $_FILES[$name[1]]['name'] = $filename[1];
                    $_FILES[$name[1]]['size'] = strlen($postDataMap[1]);
                    $_FILES[$name[1]]['type'] = preg_match('/Content-Type: ?(.*);?/s', $postDataMap[0], $contentType) ? $contentType[1] : '';
                    if (MEEPO_PS_HTTP_UPLOAD_FILE_GENERATE_TEMP_FILE && ($filename = tempnam(sys_get_temp_dir(), 'uf_'))
                        && $filename !== false && file_put_contents($filename, $postDataMap[1]) !== false
                    ) {
                        $_FILES[$name[1]]['tmp_name'] = $filename;
                    } else {
                        $_FILES[$name[1]]['file_content'] = $postDataMap[1];
                    }
                }
            }else{
                //日志
                Log::write('An illegal HTTP POST request, because the name does not exist. HTTP body: ' . $postDataMap[0], 'WARNING');
            }
        }
        //填充$_POST
        if(!empty($postUrlEncode)){
            rtrim($postUrlEncode, '&');
            parse_str($postUrlEncode, $_POST);
        }
    }

    /**
     * 获取HTTP状态码
     * @return array
     */
    public static function getHttpCode(){
        return array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
        );
    }
}
