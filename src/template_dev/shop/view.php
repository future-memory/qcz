<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0">
    <title>兑换商城</title>
    <meta content="telephone=no" name="format-detection">
    <script>
        // mzbbsClient.needWait();
    </script>
    <link rel="stylesheet" href="../../src/static_new/css/common.css">
    <link rel="stylesheet" href="../../src/static_new/css/shop/shop-view.css">
</head>
<body>
    <div class="container" id="container">
        <main :info="info"  v-show="!orderShow"></main>
        <order 
            :info="info" 
            :address-fields-map="addressFieldsMap" 
            :all-passed="allPassed"
            v-show="orderShow&&!editorShow"></order>
        <address-form :address-fields-map="addressFieldsMap" v-show="editorShow"></address-form>
    </div>

    <script type="text/template" id="mainTemplate">
        <div class="main">
            <div class="banner"><img :src="info.goods_pic"></div>
            <div class="infos">
                <div class="clearfix">
                    <h2 class="goods-name">{{info.name}}</h2>
                    <span class="goods-price"><span>{{info.price}}</span> 煤球</span><span class="goods-price--origin" v-show="info.orig_price!='0'">{{info.orig_price}} 煤球</span>
                </div>
                <div class="infos-tip">{{{info.notice}}}</div>
            </div>
            <div class="detail" v-show="info.remark">{{{info.remark}}}</div>
            <div class="btn-buy" v-bind:class="{'disable': info.count=='0'}" @click="showOrder"><span v-show="info.count!='0'">立即兑换</span><span v-show="info.count=='0'">缺货</span></div>
        </div>
    </script>

    <script type="text/template" id="orderTemplate">
        <div class="order-container">
            <div class="order-address" @click="showAddressEditor">
                <i class="icon-local"><img src="<?php echo IMG_CDN_URL4.STATICURL?>bbs_app/images/shop/icon-local.png"></i>
                <div v-if="addressFieldsMap[0].value" style="border-top: 1px solid transparent; padding: 0 10px;">
                    <p class="address-base"><span class="address-name">收货人：{{addressFieldsMap[0].value}}</span><span class="address-phone">{{addressFieldsMap[1].value}}</span></p>
                    <p class="address-detail">{{addressFieldsMap[2].value}}</p>
                </div>
                <div v-if="!addressFieldsMap[0].value">
                    <p class="address-no">请填写收件人信息</p>
                </div>
                <i class="icon-arrow"><img src="<?php echo IMG_CDN_URL4.STATICURL?>bbs_app/images/shop/icon-arrow-r.png"></i>
            </div>
            <div class="sep-sec"></div>
            <div class="control-wrap">
                <h2>兑换商品</h2>
                <div class="order-info clearfix">
                    <img :src="info.goods_pic">
                    <div class="order-info-txt">
                        <h3 class="order-name">{{info.name}}</h3>
                        <p><span class="order-price"><span>{{info.price}}</span> 煤球</span><span class="order-price--origin" v-show="info.orig_price!='0'">{{info.orig_price}} 煤球</span></p>
                        <p class="order-stock">库存：{{info.count}}个（限兑{{info.limit}}个）</p>
                    </div>
                </div>
                <div class="controls">
                    <div class="control"><span class="control-name">数量：</span><p><span class="control-minus" @click="changeNum('minus')">－</span><span class="control-num">{{info.num}}</span><span class="control-plus" @click="changeNum('plus')">＋</span></p></div>
                    <div class="control"><span class="control-name">是否归还：</span><p>{{info.need_return=="0"?"否":"是"}}</p></div>
                    <div class="control"><span class="control-name">归还周期：</span><p>{{info.cycle}}</p></div>
                    <div class="control verify">
                        <span class="control-name">验证码：</span>
                        <div class="field field-text">
                            <input type="text" v-model="mycode" name="code">
                            <a class="sendCode" @click="sendCode" id="sendCode">
                                <i class="text">{{text}}</i>
                                <em v-show="numshow" class="countdown">{{count}}</em>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
             <div class="btn-buy-commit" @click="commitOrder">确认兑换</div>
        </div>
    </script>

    <script type="text/template" id="addressFormTemplate">
        <div class="address-edit">
            <div class="fields">
                <div v-for="field in addressFieldsMap" class="field" v-bind:class="'field-'+field.type">
                    <span class="key">{{field.txt}}：</span>
                    <div class="value-input">
                        <span class="error-tips" v-if="field.showError">{{field.errorDisplay}}</span>
                        <input v-bind:readonly="field.readonly" v-model="field.value" @blur="validateIt(field)" type="text" name={{field.code}}>
                    </div>
                </div>
            <div>
            <a @click="saveAddress" class="btn-submit">保存</a>
        </div>
    </script>

    <script>
        var api_url = '/index.php';
        var uid  = parseInt("<?php echo $member['uid']; ?>");
        var info = <?php echo json_encode($info); ?>;
        info.userMb = parseInt('<?php echo $user_mb; ?>');
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?df4f24044c193a1dc6e61693ffd35cff";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
    <!-- build:js ../src/static_new/js/shop/vendor.js -->
    <script src="../../src/static_new/js/lib/underscore-min.js"></script>
    <script src="../../src/static_new/js/lib/zepto.min.js"></script>
    <script src="../../src/static_new/js/lib/vue.js"></script>
    <!-- endbuild -->

    <!-- build:js ../src/static_new/js/shop/show-view.js -->
     <script src="../../src/static_new/js/shop/shop-view.js"></script>
    <!-- endbuild  -->
</body>
</html>