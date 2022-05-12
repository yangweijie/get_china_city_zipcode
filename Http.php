<?php

/**
 * CURL数据请求管理器
 * Class Http
 * @package library\tools
 */
class Http
{
    /**
     * 以get模拟网络请求
     * @param string $url HTTP请求URL地址
     * @param array $query GET请求参数
     * @param array $options CURL参数
     * @return boolean|string
     */
    public static function get($url, $query = [], $options = [])
    {
        $options['query'] = $query;
        return self::request('get', $url, $options);
    }

    /**
     * 以get模拟网络请求
     * @param string $url HTTP请求URL地址
     * @param array $data POST请求数据
     * @param array $options CURL参数
     * @return boolean|string
     */
    public static function post($url, $data = [], $options = [])
    {
        $options['data'] = $data;
        return self::request('post', $url, $options);
    }

    /**
     * CURL模拟网络请求
     * @param string $method 请求方法
     * @param string $url 请求方法
     * @param array $options 请求参数[headers,data]
     * @return boolean|string
     */
    public static function request($method, $url, $options = [])
    {
        $curl = curl_init();
        // $key  = "http_{$url}";
        // GET 参数设置
        if (!empty($options['query'])) {
            $url .= (stripos($url, '?') !== false ? '&' : '?') . http_build_query($options['query']);
        }

        $opt = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        // 浏览器代理设置
        $opt[CURLOPT_USERAGENT] = self::getUserAgent();

        if(isset($options['proxy'])){
            $opt[CURLOPT_PROXY] = $options['proxy'];
        }

        if(isset($options['proxyport'])){
            $opt[CURLOPT_PROXYPORT] = $options['proxyport'];
        }

        // Cookie 信息设置
        if (!empty($options['cookie'])) {
            $opt[CURLOPT_COOKIE] = $options['cookie'];
        }

        if (!empty($options['cookie_file'])) {
            $opt[CURLOPT_COOKIEJAR]  = $options['cookie_file'];
            $opt[CURLOPT_COOKIEFILE] = $options['cookie_file'];
        }
        $header = $options['headers']??[];
        // POST 数据设置
        if (strtolower($method) === 'post') {
            $opt[CURLOPT_POST] = true;

            if(is_string($options['data'])){
                $opt[CURLOPT_POSTFIELDS] = $options['data'];
                $header[]                = "Content-Type:application/json";

                if($options && isset($options['headers']) && $options['headers']){
                    foreach ($options['headers'] as $key => $value) {
                        if(!in_array($value, $header)){
                            $header[] = $value;
                        }
                    }
                }
            }else{
                $opt[CURLOPT_POSTFIELDS] = self::buildQueryData($options['data']);
            }
        }
        // CURL 头信息设置
        if (!empty($header)) {
            $opt[CURLOPT_HTTPHEADER] = $header;
        }
        // 请求超时设置
        $opt[CURLOPT_TIMEOUT] = isset($options['timeout']) && is_numeric($options['timeout'])? $options['timeout']: 60;


        curl_setopt_array($curl, $opt);

        $content = curl_exec($curl);

        if(curl_errno($curl))
        {
            $error = curl_error($curl);
            self::trace($url);
            self::trace($error);
            self::trace($opt);
            // trace($opt);
            // ptrace($opt);
            // cache($key, $error);
        }else{
            if(empty($content)){
                self::trace($opt);
                self::trace($url);
                // ptrace($opt);
                // ptrace($url);
            }
        }
        curl_close($curl);
        return $content;
    }

    /**
     * POST数据过滤处理
     * @param array $data 需要处理的数据
     * @param boolean $build 是否编译数据
     * @return array|string
     */
    private static function buildQueryData($data, $build = true)
    {
        if (!is_array($data)) return $data;
        foreach ($data as $key => $value) if (is_object($value) && $value instanceof \CURLFile) {
            $build = false;
        } elseif (is_string($value) && class_exists('CURLFile', false) && stripos($value, '@') === 0) {
            if (($filename = realpath(trim($value, '@'))) && file_exists($filename)) {
                list($build, $data[$key]) = [false, new \CURLFile($filename)];
            }
        }
        return $build ? http_build_query($data) : $data;
    }


    /**
     * 获取浏览器代理信息
     * @return string
     */
    private static function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) return $_SERVER['HTTP_USER_AGENT'];
        $userAgents = [
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
        ];
        return $userAgents[array_rand($userAgents, 1)];
    }

    public static function trace($msg){
        $file = './http.log';
        if(!is_string($msg)){
            $msg = var_export($msg, true);
        }
        file_put_contents($file, $msg.PHP_EOL, FILE_APPEND);
    }
}