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
    $host      = 'http://xzqh.mca.gov.cn/';
    $url       = "{$host}{$urls[$pcode]}".urlencode(to_gbk($cname));
    $puppeteer = new \Nesk\Puphpeteer\Puppeteer([
        'executable_path'=>'/usr/local/bin/node', // 按实际的写 如果是win  写node.exe
    ]);
    $browser = $puppeteer->launch([
        'args'=>['--no-sandbox', '--disable-setuid-sandbox']
    ]);
    $zipcode = '';
    try {
        $page = $browser->newPage();
        $page->goto($url);
$js =  <<<JS
var ret = '';
ret = $('.info_table tr:eq(1) td:eq(6)').text();
return ret;
JS;
        $verifyFunction = \Nesk\Rialto\Data\JsFunction::createWithBody($js);
        $zipcode = $page->evaluate($verifyFunction);
    } catch (\Nesk\Rialto\Exceptions\Node\Exception $e) {
        ptrace("获取 省编码:{$pcode} 市名称:{$cname} 的邮编失败");
        ptrace($e->getMessage().PHP_EOL.$e->getTraceAsString());
    }
    $browser->close();
    return $zipcode;
}

function ptrace($msg){
    $file = 'app.log';
    if(!is_string($msg)){
        $msg = var_export($msg, true);
    }
    file_put_contents($file, $msg.PHP_EOL, FILE_APPEND);
}