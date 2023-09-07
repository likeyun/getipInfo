# getipInfo
获取ip地址的信息，包含国家、省份、城市、县区

# 如何使用
`getipInfo.php?ip=需要查询的IP地址`

一共有5个接口，接口失效会使用下一个接口，直到接口成功，最终输出数据格式如下：
```
{"code":200,"msg":"获取成功","ipinfo":{"country":"中国","province":"广东省","city":"广州市","district":"番禺区","ip":"xx.xx.xxx.xx"}}
```
