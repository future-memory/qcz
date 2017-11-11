/*兑换商城页*/

Vue.config.debug = true;

// backpress由客户端调用，用于通知页面：返回按钮被按下咯～
window.backpress = function() {};
//任务
function doTask() {
    $.ajax({
        url: '/index.php',
        type: 'POST',
        data: {
            mod: 'task',
            action: 'auto_complete',
            taskid: 13
        },
        dataType: 'json',
        success: function(rep){
            if(rep.code == 200) mzbbsClient.showToast(rep.data.task_msgs[0], 0);
        }
    });
}
$(function() {

    // 初始化info
    info.price = parseInt(info.price);
    info.num = 1;

    // var bbstoken = '480ePKFXCcxvllLsWwI9GZMo9I6L30pf109KlcwV6WL0g%2FEW%2BD86028%2FckyTk%2BBd%2FI';

    function isLogin() {
        return uid != '0';
    }

    // 简易弹框
    function simpleDialog(html) {
        var $body = $('body');
        var $simpleDialog = $('<div class="simple-dialog" id="simpleDialog"><div class="simple-dialog-conten"></div></div>');
        $body.append($simpleDialog);
        $simpleDialog.height($body[0].scrollHeight).show().find('.simple-dialog-conten').html(html);
        setTimeout(function() {
            $simpleDialog.hide();
        }, 2000);
    }

    // 获取用户收货地址
    $.ajax({
        url: 'https://bbs-act.meizu.cn/index.php',
        type: 'get',
        data: {
            mod:'index',
            action: 'single_address',
            // bbstoken: bbstoken,
        },
        xhrFields: { withCredentials: true },
        dataType: 'json',
        success: function(rep){
            var code = rep.code, data = rep.data;
            if(code == 200){
                if(!_.isEmpty(data)) {
                    MainVM.allPassed = true;
                    // 把数据塞进我们的结构中
                    setAddressValues(data);
                }
            } else {
                mzbbsClient.showToast(rep.message, 0);
            }
        }
    });

    // 地址表单的规格
    var addressFieldsMap = [
        {
            type: 'text',
            code: 'name',
            txt: '姓名',
            errorDisplay: '姓名只能是汉字或字母(8个字以内)',
            rules: /^[A-Za-z\u4e00-\u9fa5]{1,8}$/,
        },
        {
            type: 'text',
            code: 'phone',
            txt: '手机号',
            errorDisplay: '请填写正确的手机号',
            rules: /^1[3|4|5|7|8]\d{9}$/,
            readonly: true
        },
        {
            type: 'text',
            code: 'address',
            txt: '详细地址',
            errorDisplay: '请填写地址信息(80字以内，不包含特殊字符)',
            rules: /^[^“’]{1,80}$/,
        },
    ]
    // 增加两个字段：标示是否没有通过验证 字段的值
    _.each(addressFieldsMap, function(item, index) {
        item.showError = false;
        item.value = '';
    });

    function setAddressValues(address) {
        _.each(addressFieldsMap,function(item, index) {
            // if(item.code == 'phone') {
            //     item.value = window.bindphone;
            // } else {
            item.value = address[item.code];
            // }
        });
    }


    // 注册自定义组件（商品信息展示区）
    Vue.component('main', {
        template: '#mainTemplate',
        props: ['info'],
        methods: {
            showOrder: function() {
                // 判断库存
                if(this.info.count == '0') {
                    return;
                }
                var _this = this;
                // 登录判读
                if(!isLogin()) {
                    mzbbsClient.showDialogYesNoMsg('请先登录', 'mzbbsClient.bbsappLogin();', '');
                    return;
                }
                this.$dispatch('orderToggle', true);    // 显示订单界面
            },
        },
    });

    // 注册自定义组件（订单区）
    Vue.component('order', {
        template: '#orderTemplate',
        props: ['info','addressFieldsMap', 'allPassed'],
        data: function() {
            return {
                count: 60,
                text: '获取验证码',
                canSend: true,
                timer: '',
                numshow: false,
                mycode: ''
            }
        },
        methods: {
            sendCode: function() {
                var self = this;
                if (!this.canSend) return;
                var goods_list = [{'id': this.info.id, 'count': this.info.num}];
                $.ajax({
					url: 'https://bbs-act.meizu.cn/index.php',
					type: 'post',
					data: {mod:'index', action: 'send_code', goods_list: goods_list },
					dataType: 'json',
					xhrFields: {withCredentials: true},
					success: function(rep){
                        var code = rep.code, data = rep.data;
                        if(code == 200){
                            self.countdown();
                            self.canSend = false;
                            self.numshow = true;
                            window.mzbbsClient && mzbbsClient.showToast('发送成功', 0);
						} else if(code == 401){
							mzbbsClient.showDialogYesNoMsg('请先登录', 'mzbbsClient.bbsappLogin();', '');
						} else{
                            window.mzbbsClient && mzbbsClient.showToast(rep.message, 0);
						}
					},
					error: function() {
                        window.mzbbsClient && mzbbsClient.showToast('服务器发生未知错误，请稍后再试～', 0);
					}
			    });
            },
            countdown: function() {
                var self = this;
                this.text = '已发送，';
                self.numshow = true;
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(function(){
                    if (self.count === 0) {
                    self.resetStatus();
                    return;
                    }
                    self.count--;
                }, 1000);
            },
            resetStatus: function() {
                this.text = '获取验证码';
                this.numshow = false;
                this.canSend = true;
                this.count = 60;
                if (this.timer) clearInterval(this.timer);
            },
            // 显示地址填写控件
            showAddressEditor: function() {
                var _this = this;
                this.$dispatch('editorToggle', true);
            },
            // 更改下单商品数
            changeNum: function(type) {
                if( type == 'minus' ) {
                    if(this.info.num > 1) {
                        this.info.num--;
                    }
                } else if(this.info.num < parseInt(this.info.limit)){
                    this.info.num++;
                }
            },
            // 对每一项做验证
            validate: function() {
                var allPassed = true;
                _.each(this.addressFieldsMap,function(item, index) {
                    item.showError = !(!!item.value && item.rules.test(item.value.trim()));
                    if(item.showError) {
                        allPassed = false;
                    }
                })
                this.$dispatch('allPassed', allPassed);
                return allPassed;
            },
            // 提交订单
            commitOrder: function() {
                var _this = this;
                if(!this.validate()) {
                    // simpleDialog('请把地址信息填写完整');
                    mzbbsClient.showToast('请把地址信息填写完整', 0);
                    return;
                }
                if (this.mycode == '') {
                    mzbbsClient.showToast('请填写验证码', 0);
                    return;
                }
                var goods_list = [{'id': this.info.id, 'count': this.info.num}];
                if(this.info.num * parseInt(this.info.price) > this.info.userMb) {
                    // simpleDialog('你的煤球不够哦～');
                    mzbbsClient.showToast('你的煤球不够哦', 0);
                } else {
                    $.ajax({
                        url: api_url,
                        type: 'post',
                        data: {
                            mod:'shop',
                            action: 'submit_order',
                            name: this.addressFieldsMap[0].value,
                            phone: this.addressFieldsMap[1].value,
                            address: this.addressFieldsMap[2].value,
                            goods_list: goods_list,
                            code: this.mycode
                            // bbstoken: bbstoken,
                        },
                        dataType: 'json',
                        success: function(rep){
                            var code = rep.code, data = rep.data;
                            var input = document.getElementsByClassName('verify')[0].getElementsByTagName('input')[0];
                            if(code == 200){
                                _this.resetStatus();
                                input.value = '';
                                window.mzbbsClient && mzbbsClient.showToast('您的订单提交成功', 0);
                                _this.info.count -= _this.info.num;
                                setTimeout(function() {
                                    _this.$dispatch('orderToggle', false);
                                    doTask();
                                }, 2000);
                            } else {
                                 window.mzbbsClient && mzbbsClient.showToast(rep.message, 0);
                            }
                        },
                        error: function() {
                            // simpleDialog('服务器发生未知错误，请稍后再试～');
                            mzbbsClient.showToast('服务器发生未知错误，请稍后再试', 0);
                        }
                    });
                }
            }
        }
    })

    // 注册自定义组件（地址表单区）
    Vue.component('address-form', {
        template: '#addressFormTemplate',
        props: ['addressFieldsMap'],
        methods: {
            // 提交表单
            saveAddress: function() {
                var _this = this;
                if(this.validate()) {
                    $.ajax({
                        url: api_url,
                        type: 'post',
                        data: {
                            mod:'shop',
                            action: 'update_address',
                            name: this.addressFieldsMap[0].value,
                            phone: this.addressFieldsMap[1].value,
                            address: this.addressFieldsMap[2].value,
                            // bbstoken: bbstoken,
                        },
                        dataType: 'json',
                        success: function(rep){
                            var code = rep.code, data = rep.data;
                            if(code == 200){
                            } else {
                                mzbbsClient.showToast(rep.message, 0);
                            }
                        }
                    });


                    _this.$dispatch('editorToggle', false);
                } else {
                    mzbbsClient.showToast('请正确填写每一项', 0);
                }
            },
            // 对当前项做验证
            validateIt: function (vm) {
                var passed = vm.rules.test(vm.value);
                vm.showError = !passed;
                if(!passed) {
                    this.$dispatch('allPassed', false);
                }
            },
            // 对每一项做验证
            validate: function() {
                var allPassed = true;
                _.each(this.addressFieldsMap,function(item, index) {
                    item.showError = !(!!item.value && item.rules.test(item.value.trim()));
                    if(item.showError) {
                        allPassed = false;
                    }
                })
                this.$dispatch('allPassed', allPassed);
                return allPassed;
            }
        }
    });

    // 渲染
    window.MainVM = new Vue({
        el: '#container',
        data: {
            info: info,
            addressFieldsMap: addressFieldsMap,
            orderShow: false,
            editorShow: false,
            allPassed: false
        },
        events: {
            // 显隐订单编辑页
            'orderToggle': function(msg) {
                var _this = this;
                _this.orderShow = msg;
                if(msg) {
                    window.mzbbsClient && mzbbsClient.disableBack(true);
                    backpress = function() {
                        _this.$dispatch('orderToggle', false);
                    }
                } else {
                    window.mzbbsClient && mzbbsClient.disableBack(false);
                }
            },
            // 显隐地址编辑页
            'editorToggle': function(msg) {
                var _this = this;
                _this.editorShow = msg;
                if(msg) {
                    backpress = function() {
                        _this.$dispatch('editorToggle', false);
                    }
                }
                // 如果是关闭了地址编辑界面
                else {
                    backpress = function() {
                        _this.$dispatch('orderToggle', false);
                    }
                }
            },
            'allPassed': function(msg) {
                this.allPassed = msg;
            }
        },
        ready: function() {
            // mzbbsClient.loadingFinish('200');
        },
    })

})
