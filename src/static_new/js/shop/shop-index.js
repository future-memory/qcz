/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__(1);


/***/ }),
/* 1 */
/***/ (function(module, exports) {

	'use strict';

	/*兑换商城页*/

	// Vue.config.debug = true;

	$(function () {
	    function isLogin() {
	        return uid != '0';
	    }

	    var api_url = '/index.php';

	    $('.fun-link a').on('click', function (e) {
	        if (!isLogin()) {
	            mzbbsClient.showDialogYesNoMsg('请先登录', 'mzbbsClient.bbsappLogin();', '');
	            e.preventDefault();
	        }
	    });

	    // 加载列表数据
	    function GoodsList() {
	        this.listData = null;
	        this.listPage = 1;
	        this.listPerPage = 10;
	        this.hasMore = true;

	        this.vm = {};

	        // 注册自定义组件（商品列表）
	        Vue.component('goods-list', {
	            template: '#goodsListTemplate',
	            props: ['listData', 'hasMore']
	        });
	    }
	    GoodsList.prototype.getData = function () {
	        var _this = this;
	        $.ajax({
	            url: api_url,
	            type: 'get',
	            data: {
	                mod: 'shop',
	                action: 'goods_list',
	                page: this.listPage,
	                limit: this.listPerPage
	            },
	            dataType: 'json',
	            success: function success(rep) {
	                var code = rep.code,
	                    data = rep.data;
	                _this.hasMore = data.last_page ? false : true;
	                if (data.last_page) {
	                    ajaxLoader.hasMore = false;
	                }
	                if (code == 200) {
	                    _this.initData(data.list);
	                    // 第一次获取第一页
	                    if (_this.listPage == 1) {
	                        _this.listData = data.list;
	                        _this.renderList();
	                    } else {
	                        _this.listData.concat(data.list);
	                        _.each(data.list, function (item) {
	                            _this.listData.push(item);
	                        });
	                        ajaxLoader.bLoading = false;
	                    }

	                    if (_this.listPage * _this.listPerPage >= data.count) {
	                        _this.vm.hasMore = false;
	                        ajaxLoader.hasMore = false;
	                    }
	                } else {
	                    mzbbsClient.showToast(rep.reason, 0);
	                }
	                _this.listPage++;
	            }
	        });
	    };
	    // 对列表数据做一点处理
	    GoodsList.prototype.initData = function (listData) {
	        _.each(listData, function (item, index) {
	            item.extraInfo = null;
	            if (!(parseInt(item.count) > 0)) {
	                item.extraInfo = { type: 'soldout', txt: '缺货' };
	            } else if (item.just_for_app == "1") {
	                item.extraInfo = { type: 'cutprice', txt: 'APP专属' };
	            } else if (item.orig_price != '0' && parseInt(item.orig_price, 10) > parseInt(item.price, 10)) {
	                item.extraInfo = { type: 'cutprice', txt: '优惠' };
	            }
	        });
	    };
	    GoodsList.prototype.renderList = function () {
	        var _this = this;

	        this.vm = new Vue({
	            el: '#goodsList',
	            data: {
	                listData: _this.listData,
	                hasMore: _this.hasMore
	            }
	        });
	    };
	    window.goodsList = new GoodsList();
	    goodsList.getData();

	    // 加载更多
	    function AjaxLoadMore(loadFun) {
	        var $window = $(window);
	        var $body = $('body');
	        var _this = this;

	        this.bLoading = false;
	        this.hasMore = true;

	        $window.scroll(function (e) {
	            if (_this.hasMore && !_this.bLoading) {
	                if ($window.scrollTop() > $body[0].scrollHeight - $body.height() - 100) {
	                    _this.bLoading = true;
	                    loadFun(); // 获取新数据
	                }
	            }
	        });
	    }
	    var ajaxLoader = new AjaxLoadMore(_.bind(goodsList.getData, goodsList));

	    // 注册自定义组件（banner轮播）
	    Vue.component('swipe-banner', {
	        template: '#swipeBannerTemplate',
	        props: ['bannerData'],
	        ready: function ready() {
	            var mySwiper = new Swiper('#banner', {
	                centeredSlides: true,
	                loop: true,
	                loopAdditionalSlides: 1,
	                slidesPerView: 1,
	                slidesPerGroup: 1,
	                paginationType: 'bullets',
	                pagination: '.swiper-pagination',
	                initialSlide: 0,
	                spaceBetween: 10,
	                noSwiping: false,
	                autoplay: 4000,
	                speed: 500,
	                autoplayDisableOnInteraction: false,
	                onSlideChangeEnd: function onSlideChangeEnd(swiper) {}
	            });
	        }
	    });
	    new Vue({
	        el: '#bannerWrap',
	        data: {
	            bannerData: promote_data
	        }
	    });
	});

/***/ })
/******/ ]);