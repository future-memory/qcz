<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=640,user-scalable=no, initial-scale=1.0" name="viewport">
    <title>兑换记录</title>
    <meta content="telephone=no" name="format-detection">
    <script>
        mzbbsClient.needWait();
    </script>
    <script>
    var phoneScale = parseInt(window.screen.width)/640;
    document.write('<meta name="viewport" content="width=640, minimum-scale = '+ phoneScale +', maximum-scale = '+ phoneScale +'">');
    </script>
    <link rel="stylesheet" href="//res.qingchuzhang.com/css/common-e59771e06c.css">
    <style>
    html,body {
        background-color: #fafafa;
    }
    .container {
        position: relative;
        min-height: 100%;
        width: 100%;
    }
    .orders-empty {
        font-size: 36px;
        text-align: center;
        position: absolute;
        top: 40%;
        left: 50%;
        width: 100%;
        color: #515151;
        -webkit-transform: translate(-50%,-50%);
        transform: translate(-50%,-50%);
    }
    .orders li {
        height: 178px;
        padding: 36px;
        border-bottom: 1px solid #f0f0f0;
        overflow: hidden;
    }
    .order-pic {
        float: left;
        width: 176px;
        height: 176px;
        margin-right: 18px;
        border: 1px solid #d2d2d2;
    }
    .order-pic img {
        width: 100%;
        height: 100%;
    }
    .order-txt {
        /*margin-left: 234px;*/
    }
    .order-time {
        float: right;
        color: #AAA;
        font-size: 18px;
    }
    .order-name {
        padding-top: 20px;
        font-size: 26px;
    }
    .order-address {
        margin-top: 10px;
        color: #777;
        font-size: 22px;
        line-height: 1.6;
    }
    .dividing-line {
        position: relative;
        font-size: 20px;
        top: -3px;
    }
    </style>
</head>
<body>
    <div class="container" id="container">
        <orders :data-list="dataList"></orders>
    </div>

    <script type="text/template" id="ordersTemplate">
        <div class="orders" id="orders">
            <p class="orders-empty" v-show="!dataList.length">您暂时没有兑换任何商品</p>
            <ul v-show="dataList">
                <li v-for="item in dataList">
                    <div class="order-pic"><img :src="item.goods.cover_pic" alt=""></div>
                    <div class="order-txt">
                        <p class="order-time">{{item.dateline | dateConventer}}</p>
                        <p class="order-name">{{item.goods.name}} x {{item.goods.count}}</p>
                        <div class="order-address">
                            <p><span>{{item.name}}</span><span class="dividing-line"> | </span><span>{{item.phone}}</span></p>
                            <p>{{item.address}}</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </script>
</body>

<script>
    var api_url = '/index.php';
</script>

 <script src="//res.qingchuzhang.com/js/vendor-c2c0ff639e.js"></script>
<script>
    // var bbstoken = '4666iB%2F2%2BybvmSdVnx7ka941MAhYXhFZpG1CHGZfxBxwFpU0ZfFn4qXX6yzY6v1UEd0';
    $(function () {

        // 过滤器--时间转换
        Vue.filter('dateConventer', function (date) {
            date = new Date(date*1000);
            return date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日';
        })

        Vue.component('orders', {
            template: '#ordersTemplate',
            props: ['dataList'],
        })

        $.ajax({
            url: api_url,
            type: 'get',
            data: {
                mod:'shop', 
                action: 'my', 
                // bbstoken: bbstoken, 
            },
            dataType: 'json',
            success: function(rep){
                var code = rep.code, data = rep.data, dataList = data.list;
                if(code == 200) {
                    _.each(dataList, function(item) {
                        item.goods = item.goods[0]; // 先只取第一条
                    })
                    renderList(dataList);
                    mzbbsClient.loadingFinish('200');
                }
            }
        });

        function renderList(dataList) {
            new Vue({
                el: '#container',
                data: {
                    dataList: dataList,
                }
            })
        }
    })
</script>
</html>