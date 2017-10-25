<?php

/*
* @author: 4061470@qq.com
*/

final class HelperQiniu
{
    /*
     * 文件存储模块
     */
    private static $modules = array('app', 'forum', 'album', 'misc', 'temp', 'shop', 'live');

    /*
     * 默认图片模块
     */
    private static $default_module = 'misc';

 
    public static function sign($bucket, $module, &$key) 
    {
        // 后台允许上传所有模块
        // $module = in_array($module, $this->public_modules) ? $module : $this->default_module;
        $module   = in_array($module, self::$modules) ? $module : self::$default_module;

        $path     = $module.'/'.date('Y/m/d/His');
        $ext      = substr($key, strrpos($key, '.'));
        $key      = $path.HelperAuth::random(6).$ext;
        $deadline = time() + QN_EXPIRE;
        $scope    = $bucket . ':' . $key;

        $policy = null;
        // array('returnBody'=>array(
        //     'name'   => '$(fname)',
        //     'size'   => '$(fsize)',
        //     'w'  => '$(imageInfo.width)',
        //     'h' => '$(imageInfo.height)',
        //     'hash'   => '$(etag)'
        // ));

        $args = self::copyPolicy($args, $policy, $strictPolicy = true);
        $args['scope'] = $scope;
        $args['deadline'] = $deadline;

        $b = json_encode($args);
        return self::signWithData($b);
    }

    public static function signData($data)
    {
        $hmac = hash_hmac('sha1', $data, QN_SECRET, true);
        return QN_ACCESS . ':' . self::base64_urlSafeEncode($hmac);
    }

    public static function signWithData($data)
    {
        $encodedData = self::base64_urlSafeEncode($data);
        return self::signData($encodedData) . ':' . $encodedData;
    }


    /**
     *上传策略，参数规格详见
     *http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
     */
    private static $policyFields = array(
        'callbackUrl',
        'callbackBody',
        'callbackHost',
        'callbackBodyType',
        'callbackFetchKey',

        'returnUrl',
        'returnBody',

        'endUser',
        'saveKey',
        'insertOnly',

        'detectMime',
        'mimeLimit',
        'fsizeMin',
        'fsizeLimit',

        'persistentOps',
        'persistentNotifyUrl',
        'persistentPipeline',

        'deleteAfterDays',
        'fileType',
        'isPrefixalScope',
    );

    private static function copyPolicy(&$policy, $originPolicy, $strictPolicy)
    {
        if ($originPolicy === null) {
            return array();
        }
        foreach ($originPolicy as $key => $value) {
            if (!$strictPolicy || in_array((string)$key, self::$policyFields, true)) {
                $policy[$key] = $value;
            }
        }
        return $policy;
    }

    public static function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

}

