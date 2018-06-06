<?php
if (!function_exists('stringArr2Arr')) {
    function stringArr2Arr($stringArr)
    {
        return array_map('trim', explode(',', trim($stringArr, ' []')));
    }
}

/**
 * @param        $url
 * @param array $params
 * @param string $method
 * @param array $header
 *
 * @return mixed
 */
if (!function_exists('http')) {
    function http($url, $params = [], $method = 'get', $header = [])
    {
        $opts = [
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36',
        ];
        switch ($method) {
            case 'get' :
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'post' :
                // 判断是否传输文件
                $params = is_array($params) ? http_build_query($params) : $params;
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default :
                exit('不支持的请求方式！');
        }
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            exit('请求发生错误：' . $error);
        }

        return $data;
    }
}


/**
 * @param $str
 *
 * @return false|int
 */
if (!function_exists('is_mobile')) {
    function is_mobile($str)
    {
        return preg_match('/^1[34578]\d{9}$/', $str);
    }
}
/**
 * @param $str
 *
 * @return false|int
 */
if (!function_exists('is_email')) {
    function is_email($str)
    {
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str);
    }
}
// 授权封装
if (!function_exists('auth')) {
    function auth($user_id, $path_info, $version, $system_id)
    {
        return boolval(\Rbac\models\RoleRule::hasAuth($user_id, $path_info, $version, $system_id));
    }
}
//二维数组冒泡排序
if (!function_exists('bubbleSort')) {
    function bubbleSort($arr, $field = "yearMonth", $sort = 0)
    {
        $count = count($arr);//统计数组的个数
        for ($i = 0; $i < $count; $i++) {//循环判断
            for ($j = 0; $j < $count - $i - 1; $j++) {//遍历主循环外的其他元素

                if ($sort == 1) {
                    if ($arr[$j][$field] < $arr[$j + 1][$field]) {//倒序
                        //交换
                        $temp = $arr[$j];
                        $arr[$j] = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }
                } else {
                    if ($arr[$j][$field] > $arr[$j + 1][$field]) {//正序
                        //交换
                        $temp = $arr[$j];
                        $arr[$j] = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }

                }


            }
        }

        return $arr;
    }
}
// 请求
if (!function_exists('request')) {
    function request()
    {
        return Yii::$app->request;
    }
}
//回复
if (!function_exists('response')) {
    function response()
    {
        return Yii::$app->response;
    }
}
//缓存
if (!function_exists('cache')) {
    function cache()
    {
        return Yii::$app->cache;
    }
}
//缓存-获取缓存key
if (!function_exists('getCachekey')) {

    function getCachekey($key = null, $is_encrypt = true)
    {
        if ($key == null) {
            $key = request()->pathInfo . serialize(request()->post()) . serialize(request()->get());
            $is_encrypt = true;
        }

        if ($is_encrypt) {
            $key = md5($key);
        }
        return $key;
    }
}
//缓存-是否开启缓存
if (!function_exists('is_cache')) {
    function is_cache()
    {
        if (Yii::$app->params['cache_data']['is_cache']) {
            return true;
        } else {
            return false;
        }
    }
}
//缓存-判断是否存在该缓存
if (!function_exists('hasCache')) {
    function hasCache($key)
    {
        var_dump(is_cache());
        if (is_cache()) {
            return Yii::$app->cache->exists($key);
        } else {
            return false;
        }
    }
}
//缓存-获取缓存
if (!function_exists('getCache')) {
    function getCache($key)
    {
        if (is_cache()) {
            return Yii::$app->cache->get($key);
        } else {
            return NULL;
        }

    }
}
//缓存-设置缓存
if (!function_exists('setCache')) {
    function setCache($key, $data, $valid_time = null)
    {
        if (is_cache()) {
            if ($valid_time == null) {
                $valid_time = intval(Yii::$app->params['cache_data']['valid_time']);
            }
            return Yii::$app->cache->set($key, $data, $valid_time);
        }
    }
}
//接收数据处理
if (!function_exists('input')) {
    function input($arg = null, $default = null, $method_type = '')
    {
        try {
            $arr = null;
            $request = request();
            $method_type = strtolower($method_type);
            if ($arg) {
                if ($method_type) {
                    return $request->$method_type($arg, $default);
                } else {
                    $header = $request->getHeaders()->toArray();
                    if (!isset($header[$arg])) {
                        if ($request->isPost) {
                            return $request->post($arg, $default);
                        } else {
                            return $request->get($arg, $default);
                        }

                    } else {
                        if ($header) {
                            return $header[$arg][0];
                        } else {
                            return $default;
                        }

                    }

                }


            } else {

                if ($method_type) {
                    $parm = $request->$method_type();
                    if ($parm) {
                        foreach ($parm as $pk => $pv) {
                            $arr[$pk] = $pv;
                        }
                    }
                } else {

                    $post = $request->post();
                    if ($post) {
                        foreach ($post as $pk => $pv) {
                            $arr[$pk] = $pv;
                        }
                    }

                    $get = $request->get();
                    if ($get) {
                        foreach ($get as $gk => $gv) {
                            $arr[$gk] = $gv;
                        }
                    }

                    $headers = $request->headers;
                    if ($headers) {
                        foreach ($headers as $hk => $hv) {
                            $arr[$hk] = @array_shift($hv);
                        }
                    }
                }
                if ($arr) {
                    return $arr;
                } else {
                    return $default;
                }

            }
        } catch (\Exception $exception) {
            return null;

        }
    }

}
//是否是超管
if (!function_exists('is_super_admin')) {
    function is_super_admin($user_id)
    {

        return in_array($user_id, Yii::$app->params['super_admin_id']);

    }

}
//请求回复
if (!function_exists('apiRepose')) {
    function apiRepose($data = [], $error_code, $msg = null, $statusCode = 200, $header = array())
    {
        $response = response();
        //设置返回头
        if (!empty($header)) {
            $headers = $response->headers;
            foreach ($header as $k => $v) {
                if ($k && $v) {
                    $headers->set($k, $v);
                }
            }
        }

        //处理头部
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->statusCode = $statusCode;
        if (empty($msg)) {
            $definds = @(get_defined_constants(true)['user']);
            $definds_flip = @array_flip($definds);
            if (isset($definds_flip[$error_code])) {
                if (isset($definds[$definds_flip[$error_code] . '_LANG']))
                    $msg = $definds[$definds_flip[$error_code] . '_LANG'];
            }
        }

        //处理返回数据
        $response->data =
            [
                "error_code" => sprintf("%05d", $error_code),
                "message" => $msg,
                "data" => $data,
            ];
        Yii::info($response->data);
        $response->send();
        exit;
    }
}
//接口成功操作
if (!function_exists('apiSuccess')) {

    function apiSuccess($data = [], $error_code = SUCCESS, $msg = "", $statusCode = 200, $header = array())
    {
        if (is_array($data) || is_object($data)) {
            return apiRepose($data, $error_code, $msg, $statusCode, $header);
        } else {
            if (is_array($error_code) || is_object($error_code)) {
                return apiRepose($error_code, $data, $msg, $statusCode, $header);
            } else {
                return apiRepose([], $data, $msg, $statusCode, $header);
            }

        }
    }


}
//接口失败操作
if (!function_exists('apiFail')) {
    function apiFail($data = [], $error_code = FAIL, $msg = "", $statusCode = 200, $header = array())
    {
        if (is_array($data) || is_object($data)) {

            return apiRepose($data, $error_code, $msg, $statusCode, $header);
        } else {

            if (is_array($error_code) || is_object($error_code)) {
                return apiRepose($error_code, $data, $msg, $statusCode, $header);
            } else {
                return apiRepose([], $data, $msg, $statusCode, $header);
            }

        }


    }
}

