<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>Dashboard - ACT Admin</title>

    <meta name="description" content="overview &amp; stats" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/font-awesome/4.5.0/css/font-awesome.min.css" />

    <!-- page specific plugin styles -->

    <!-- text fonts -->
    <link rel="stylesheet" href="assets/css/fonts.googleapis.com.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
      <link rel="stylesheet" href="assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="assets/css/ace-skins.min.css" />
    <link rel="stylesheet" href="assets/css/ace-rtl.min.css" />

    <!--[if lte IE 9]>
      <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- ace settings handler -->
    <script src="assets/js/ace-extra.min.js"></script>
    <style>
      .page-content {
        padding: 0;
      }
      .page-content iframe {
        width: 100%;
        height: 100%;
        border: none;
      }
    </style>
    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="assets/js/html5shiv.min.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body class="no-skin">
    <div class="s_loading" style="display:none;left:0;top:0;z-index: 1;position:fixed;width:100%;height:100%;" id="s_loading">
      <div class="inner" style="width:108px;height:108px;position:absolute;left:50%;top:50%;margin-left:-54px;margin-top:-54px;background-image:url(assets/images/loading/spiffygif_108x108.png);overflow:hidden;"></div>
    </div>
    <div id="navbar" class="navbar navbar-default          ace-save-state">
      <div class="navbar-container ace-save-state" id="navbar-container">
        <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
          <span class="sr-only">Toggle sidebar</span>

          <span class="icon-bar"></span>

          <span class="icon-bar"></span>

          <span class="icon-bar"></span>
        </button>

        <div class="navbar-header pull-left">
          <a href="#" class="navbar-brand">
            <small>
              <i class="fa fa-leaf"></i>
              ACT Admin
            </small>
          </a>
        </div>

        <div class="navbar-buttons navbar-header pull-right" role="navigation">
          <ul class="nav ace-nav">
            <li class="light-blue dropdown-modal">
              <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                <span>
                  <?php echo $member['username']; ?>
                </span>

                <i class="ace-icon fa fa-caret-down"></i>
              </a>

              <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                <li>
                  <a href="index.php?action=logout">
                    <i class="ace-icon fa fa-power-off"></i>
                    登出
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="main-container ace-save-state" id="main-container">
      <script type="text/javascript">
        try{ace.settings.loadState('main-container')}catch(e){}
      </script>

      <div id="sidebar" class="sidebar                  responsive                    ace-save-state">
        <script type="text/javascript">
          try{ace.settings.loadState('sidebar')}catch(e){}
        </script>

        <div class="sidebar-shortcuts" id="sidebar-shortcuts">

          <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
            <span class="btn btn-success"></span>

            <span class="btn btn-info"></span>

            <span class="btn btn-warning"></span>

            <span class="btn btn-danger"></span>
          </div>
        </div>

        <ul class="nav nav-list">
          <li class="active">
            <a href="">
              <i class="menu-icon fa fa-tachometer"></i>
              <span class="menu-text"> Dashboard </span>
            </a>

            <b class="arrow"></b>
          </li>
          
          <?php
          foreach ($menu_list['menu'] as $key => $value) {
            if(empty($value['submenu'])){
              continue;
            }
          ?>
          <li class="">
            <a href="#" class="dropdown-toggle">
              <i class="menu-icon fa fa-list"></i>
              <span class="menu-text"> <?=$value['name']?> </span>

              <b class="arrow fa fa-angle-down"></b>
            </a>

            <b class="arrow"></b>

            <ul class="submenu">
          <?php
            foreach ($value['submenu'] as $_key => $_value) {
          ?>
              <li class="">
                <a href="javascript:;" data-href="<?=$_value['url']?>">
                  <i class="menu-icon fa fa-caret-right"></i>
                  <?=$_value['name']?>
                </a>

                <b class="arrow"></b>
              </li>
          <?php
            }
          ?>
            </ul>
          </li>
          <?php
          }
          ?>

        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
          <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
      </div>

      <div class="main-content">
        <div class="main-content-inner">
          <div class="page-content">
              <iframe id="iframe" src="index.php?action=info"></iframe>
          </div>
      </div>
    </div>

    <!-- basic scripts -->

    <!--[if !IE]> -->
    <script src="assets/js/jquery-2.1.4.min.js"></script>

    <!-- <![endif]-->

    <!--[if IE]>
<script src="assets/js/jquery-1.11.3.min.js"></script>
<![endif]-->
    <script type="text/javascript">
      if('ontouchstart' in document.documentElement) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
    </script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- page specific plugin scripts -->

    <!--[if lte IE 8]>
      <script src="assets/js/excanvas.min.js"></script>
    <![endif]-->
    <script src="assets/js/jquery-ui.custom.min.js"></script>
    <script src="assets/js/jquery.ui.touch-punch.min.js"></script>
    <script src="assets/js/jquery.easypiechart.min.js"></script>
    <script src="assets/js/jquery.sparkline.index.min.js"></script>
    <script src="assets/js/jquery.flot.min.js"></script>
    <script src="assets/js/jquery.flot.pie.min.js"></script>
    <script src="assets/js/jquery.flot.resize.min.js"></script>

    <!-- ace scripts -->
    <script src="assets/js/ace-elements.min.js"></script>
    <script src="assets/js/ace.min.js"></script>

    <!-- inline scripts related to this page -->
    <script type="text/javascript">
      jQuery(function($) {
        $(window).on('resize', function() {
          var $content = $('.page-content');
          $content.height($(this).height() - 49);
        }).resize();
        var navList = $('ul.nav-list');
        navList.on('click', 'a', function() {
          var self = $(this);
          var url = self.data('href');
          var list = self.closest('li');
          var iframe = $('.page-content iframe');

          iframe.attr('src', url);

          navList.find('li').removeClass('active');
          list.addClass('active');
          self.parents('li').addClass('active');
        });



      });
      var loading = jQuery('#s_loading');
      var loading_timer = null;
      function show_loading() {
        loading.show();
        run_loading();
      }
      function hide_loading() {
        loading.hide();
        loading_timer && clearInterval(loading_timer)
      }

      function run_loading() {
        var img = new Image();
        var _img = loading.find('.inner');
        img.src='assets/images/loading/spiffygif_108x108.png';
        img.onload = function() {
          var per = 108;
          var step = img.height / 108;
          var i = 0;

          loading_timer = setInterval(function() {
            per += 108;
            i++;
            if(i > 18) {
              i = 0;
              per = 108;
            }
            _img.css('background-position-y', per);
          }, 30);
          
        }
      }
      
    </script>
  </body>
</html>
