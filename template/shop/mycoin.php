<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0">
    <title>我的煤球</title>
    <meta content="telephone=no" name="format-detection">
    <link rel="stylesheet" href="//res.qingchuzhang.com/css/common-e59771e06c.css">
    <style>
    .container {
        position: relative;
        width: 100%;
        height: 100%;
    }
    .my-coin-page {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background-color: #FFF;
    }
    .icon-meiqiu {
        width: 20%;
        margin: 30px auto 10px;
    }
    .icon-meiqiu img {
        width: 100%;
    }
    .my-coin-num {
        margin-top: 10px;
        text-align: center;
        font-size: 18px;
    }
    .my-coin-intro {
        margin: 30px;
        padding: 20px;
        background-color: #20c794;
        color: #FFF;
        border-radius: 16px;
    }
    .my-coin-intro h2 {
        text-align: center;
        font-weight: normal;
        font-size: 18px;
        margin: 0 auto 16px;
    }
    .my-coin-intro ol {
        padding-left: 30px;
    }
    .my-coin-intro li {
        list-style-type: decimal;
        margin-bottom: 6px;
    }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="my-coin-page">
            <div class="icon-meiqiu"><img src="<?php echo IMG_CDN_URL4.STATICURL?>bbs_app/images/shop/icon-meiqiu.png" /></div>
            <p class="my-coin-num">我的煤球：<span id="mq">0</span></p>
            <div class="my-coin-intro">
                <h2>获得煤球的途径介绍</h2>
                <ol>
                    <li>优秀帖子被版主奖励所获得</li>
                    <li>优秀帖子被论坛加为精华将获得 20 煤球的鼓励</li>
                    <li>魅友广场 每天有签到帖，每天跟帖签到将获得 10 煤球</li>
                    <li>论坛签到每连续 5 天，可获得 10 煤球的奖励</li>
                    <li>首次注册魅族手机产品将赠送 50 煤球，再次注册魅族手机产品将不再赠送煤球</li>
                    <li>积极参加论坛活动专用帖将获得活动奖励</li>
                    <li>参加幸运 3+2 抽奖，也可以获得煤球哦，马上参加</li>
                </ol>
            </div>
        </div>
    </div>
     <script src="//res.qingchuzhang.com/js/lib/zepto-1abd55c514.min.js"></script>
    <script>
        (function($){
            var mq = $('#mq');
            $.ajax({
                type: 'GET',
                cache: false,
                dataType: 'json',
                url: '/index.php?mod=shop&action=mq',
                success: function(result) {
                    if(result.code == 200 && result.data) {
                        mq.text( result.data.mq);
                    }
                }
            });
        })(window.Zepto);
    </script>
</body>
</html>