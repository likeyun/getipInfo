<?php

    // 编码
    header('Content-type:application/json');
    
    $ip = $_GET['ip'];
    
    // 过滤空数据
    if(!$ip) {
        
        $ipinfo = array(
            'code' => 201,
            'msg' => '未传入ip地址'
        );
        echo json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 验证ipv4地址合法性
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        
        $ipinfo = array(
            'code' => 201,
            'msg' => '这不是一个正确的ip地址'
        );
        echo json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 请求接口
    $methods = [
        'getMethod_1',
        'getMethod_2',
        'getMethod_3',
        'getMethod_4',
        'getMethod_5'
    ];
    
    foreach ($methods as $method) {
        $response = json_decode($method($ip));
        if ($response->code === 200) {
            
            // 如果请求成功，输出请求结果并停止循环
            echo $method($ip);
            break;
        }
    }
    
    if (!isset($response) || $response->code !== 200) {
        
        $ipinfo = array(
            'code' => 201,
            'msg' => '请求失败~'
        );
        echo json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // HTTP请求封装
    function cUrlGetIP($url) {
        
        // cUrl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        return curl_exec($ch);
        curl_close($ch);
    }
    
    // 中国34个省级行政区域
    $provinces = array(
        "北京",
        "天津",
        "河北",
        "山西",
        "内蒙古",
        "辽宁",
        "吉林",
        "黑龙江",
        "上海",
        "江苏",
        "浙江",
        "安徽",
        "福建",
        "江西",
        "山东",
        "河南",
        "湖北",
        "湖南",
        "广东",
        "广西",
        "海南",
        "重庆",
        "四川",
        "贵州",
        "云南",
        "西藏",
        "陕西",
        "甘肃",
        "青海",
        "宁夏",
        "新疆",
        "香港",
        "澳门",
        "台湾"
    );
    
    // 接口1
    // http://ipshudi.com/{ip}.htm
    function getMethod_1($ip) {
        
        $response = file_get_contents('http://ipshudi.com/'.$ip.'.htm');
        $str1 = substr($response, strripos($response, "归属地"));
        $str2 = substr($str1, 0, strrpos($str1, "运营商"));
        $str3 = substr($str2, strripos($str2, "<span>") + 6);
        $str4 = substr($str3, 0, strripos($str3, "</span>") + 6);
        
        // 提取国家
        $country = substr($str4, 0, strpos($str4, ' '));
        
        // 提取省份
        $str5 = substr($str4, 0, strrpos($str4, " <a href"));
        $province = substr($str5, strpos($str5, ' ') + 1);
        
        // 提取城市
        preg_match('/>([^<]+)</', $str4, $matches);
        $city = $matches[1];
        
        // 提取县区
        $str6 = substr($str4, strripos($str4, "</a>"));
        $district = preg_replace('/[^\x{4e00}-\x{9fa5}]+/u', '', $str6);
        
        // 判断是否获取成功
        if($country || $province || $city || $district) {
            
            // 拼接数组
            $ipinfo = array(
                'code' => 200,
                'msg' => '获取成功',
                'ipinfo' => array(
                    'country' => $country,
                    'province' => $province,
                    'city' => $city,
                    'district' => $district,
                    'ip' => $ip
                )
            );
        }else {
            
            $ipinfo = array(
                'code' => 201,
                'msg' => '获取失败'
            );
        }
        
        return json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
    }
    
    // 接口2
    // https://searchplugin.csdn.net/api/v1/ip/get?ip={ip}
    function getMethod_2($ip) {
        
        $response = cUrlGetIP('https://searchplugin.csdn.net/api/v1/ip/get?ip='.$ip);
        $code = json_decode($response,true)['code'];
        
        if($code == 200) {
            
            $str1 = json_decode($response,true)['data']['address'];
            
            // 国家
            $country = explode(' ', $str1)[0];
            
            // 省份
            $province = explode(' ', $str1)[1];
            
            // 城市
            $city = explode(' ', $str1)[2];
            
            // 县区
            $district = '';
            
            // 判断是否获取成功
            if($country || $province || $city || $district) {
                
                // 拼接数组
                $ipinfo = array(
                    'code' => 200,
                    'msg' => '获取成功',
                    'ipinfo' => array(
                        'country' => $country,
                        'province' => $province,
                        'city' => $city,
                        'district' => $district,
                        'ip' => json_decode($response,true)['data']['ip']
                    )
                );
            }else {
                
                $ipinfo = array(
                    'code' => 201,
                    'msg' => '获取失败'
                );
            }
        }else {
            
            $ipinfo = array(
                'code' => 201,
                'msg' => '获取失败'
            );
        }
        
        return json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
    }
    
    // 接口3
    // https://ipchaxun.com/{ip}/
    function getMethod_3($ip) {
        
        $response = cUrlGetIP('https://ipchaxun.com/'.$ip.'/');
        $str1 = substr($response, strripos($response, "归属地") + 15);
        $str2 = substr($str1, 0, strrpos($str1, "运营商"));
        
        // 提取省份
        global $provinces;
        foreach ($provinces as $province_) {
            if (strpos($str2, $province_) !== false) {
                $province = $province_;
                break;
            }
        }
        
        // 提取国家
        $str3 = substr($str2, 0, strrpos($str2, $province));
        $country = preg_replace('/[^\x{4e00}-\x{9fa5}]+/u', '', $str3);
        
        // 提取城市
        $str4 = substr($str2, strripos($str2, "nofollow") + 10);
        $city = substr($str4, 0, strrpos($str4, "</a>"));
        
        // 提取县区
        $str6 = substr($str2, strripos($str2, "</a>") + 4);
        $district = substr($str6, 0, strrpos($str6, "</span>"));
        
        // 判断是否获取成功
        if($country || $province || $city || $district) {
            
            // 拼接数组
            $ipinfo = array(
                'code' => 200,
                'msg' => '获取成功',
                'ipinfo' => array(
                    'country' => $country,
                    'province' => $province,
                    'city' => $city,
                    'district' => $district,
                    'ip' => $ip
                )
            );
        }else {
            
            $ipinfo = array(
                'code' => 201,
                'msg' => '获取失败'
            );
        }
        
        return json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
    }
    
    // 接口4
    // https://api.vvhan.com/api/getIpInfo?ip={ip}
    function getMethod_4($ip) {
        
        $response = cUrlGetIP('https://api.vvhan.com/api/getIpInfo?ip='.$ip);
        $success = json_decode($response,true)['success'];
        
        if($success == true) {
            
            $str1 = json_decode($response,true)['info'];
            
            // 国家
            $country = $str1['country'];
            
            // 省份
            $province = $str1['prov'];
            
            // 城市
            $city = $str1['city'];
            
            // 县区
            $district = '';
            
            // 判断是否获取成功
            if($country || $province || $city || $district) {
                
                // 拼接数组
                $ipinfo = array(
                    'code' => 200,
                    'msg' => '获取成功',
                    'ipinfo' => array(
                        'country' => $country,
                        'province' => $province,
                        'city' => $city,
                        'district' => $district,
                        'ip' => $ip
                    )
                );
            }else {
                
                $ipinfo = array(
                    'code' => 201,
                    'msg' => '获取失败'
                );
            }
        }else {
            
            $ipinfo = array(
                'code' => 201,
                'msg' => '获取失败'
            );
        }
        
        return json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
    }
    
    // 接口5
    // https://c.runoob.com/wp-content/themes/toolrunoob2/option/ajax.php?type=checkIP&REMOTE_ADDR={ip}
    function getMethod_5($ip) {
        
        $response = cUrlGetIP('https://c.runoob.com/wp-content/themes/toolrunoob2/option/ajax.php?type=checkIP&REMOTE_ADDR='.$ip);

        $flag = json_decode($response,true)['flag'];
        
        if($flag == true) {
            
            $str1 = json_decode($response,true)['data'];
            
            // 国家
            $country = $str1['country'];
            
            // 省份
            $province = $str1['regionName'];
            
            // 城市
            $city = $str1['city'];
            
            // 县区
            $district = '';
            
            // 判断是否获取成功
            if($country || $province || $city || $district) {
                
                // 拼接数组
                $ipinfo = array(
                    'code' => 200,
                    'msg' => '获取成功',
                    'ipinfo' => array(
                        'country' => $country,
                        'province' => $province,
                        'city' => $city,
                        'district' => $district,
                        'ip' => $ip
                    )
                );
            }else {
                
                $ipinfo = array(
                    'code' => 201,
                    'msg' => '获取失败'
                );
            }
        }else {
            
            $ipinfo = array(
                'code' => 201,
                'msg' => '获取失败'
            );
        }
        
        return json_encode($ipinfo,JSON_UNESCAPED_UNICODE);
    }

?>
