# 获取直辖市下区的邮编

数据来源：http://xzqh.mca.gov.cn/defaultQuery

## 实现方式

### 抓取html

curl url

symfony/dom-crawler 解析body $('.info_table tr:eq(1) td:eq(6)').text();

由于网站返回的是非标准table  css selector 获取失败

### 无头浏览器

模拟访问 url

js jquery  获取值

$('.info_table tr:eq(1) td:eq(6)').text();

## 注意

composer install 后执行  `npm install @nesk/puphpeteer `

动态的url 参数要先utf-8 转gbk 后 再 urlencode 拼接  没办法谁叫来源网站是gbk的上古网站。


直辖市编码

```
'110000', // 北京
'120000', // 天津
'310000', // 上海
'500000', // 重庆
```

## 执行

`cd dir && php index.php`

`echo get_zipcode_from_city(110000, '东城区');`