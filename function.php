<?php

function to_gbk($str){
    return iconv('utf-8', 'gbk', $str);
}

function get_zipcode_from_city($pcode, $cname){
    $urls = [
        '110000'=>'defaultQuery?shengji='.urlencode(to_gbk('北京市（京）')).'&diji='.urlencode(to_gbk('北京市')).'&xianji=',
        '120000'=>'defaultQuery?shengji='.urlencode(to_gbk('天津市（津）')).'&diji='.urlencode(to_gbk('天津市')).'&xianji=',
        '310000'=>'defaultQuery?shengji='.urlencode(to_gbk('上海市（沪）')).'&diji='.urlencode(to_gbk('上海市')).'&xianji=',
        '500000'=>'defaultQuery?shengji='.urlencode(to_gbk('重庆市（渝）')).'&diji='.urlencode(to_gbk('重庆市')).'&xianji=',
    ];
    $host    = 'http://xzqh.mca.gov.cn/';
    $url     = "{$host}{$urls[$pcode]}".urlencode(to_gbk($cname));
    // ptrace($url);
    // return $url;
    $content = Http::post($url);
    if($content){
        $content = iconv('gbk', 'utf-8', $content);
        ptrace($content);
        $crawler  = new \Symfony\Component\DomCrawler\Crawler($content);
        $result   = [];
        try {
            $ele = $crawler->filter('.info_table');
            ptrace($ele);
            ptrace($ele->html());
            return $ele->html();
        } catch (\Exception $e) {
            ptrace($e->getMessage().PHP_EOL.$e->getTraceAsString());
            ptrace("获取 pcode:{$pcode}, 市:{$cname} 的邮编失败");
            return '';
        }
    }else{
        return '';
    }
}

function ptrace($msg){
    $file = 'app.log';
    if(!is_string($msg)){
        $msg = var_export($msg, true);
    }
    file_put_contents($file, $msg.PHP_EOL, FILE_APPEND);
}