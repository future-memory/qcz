<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>管理中心-登录</title>
	<link href="assets/css/login.css" rel="stylesheet" />
</head>
<body>

<div id="wrap" class="bd">
	<div class="login-form" id="login-form">
		<form id="LoginForm" action="./index.php?mod=index&action=dologin" method="post">
			 <input type="hidden" name="refer" value="<?php //echo $refer; ?>" />
			<div class="frm">
				<label for="">帐&nbsp;&nbsp;号</label>
				<div class="ipt-uname"></div>
				<input type="text" class="ipt" placeholder="默认帐号为admin" name="username" id="username"  value=""/>
			</div>
			<div class="frm">
				<label for="userpass">密&nbsp;&nbsp;码</label>
				<div class="ipt-pwd"></div>
				<input type="password" class="ipt" placeholder="默认密码为admin888" password-placeholder="默认密码为admin888" name="userpass" id="userpass"  />
			</div>
			<div id="vcode-div" class="frm mb5" <?php echo $need_vcode ? '' : 'style="display:none;"'; ?>>
				<label for="">验证码</label>
				<input type="text" class="ipt-s" id="vcode" name='vcode' maxlength="4" autocomplete="off"  />
				<img src="./index.php?mod=index&action=vcode" id="vcode-img" width="55" height="22" />
				<a id="vcode-refresh" href="javascript:;" >看不清，换一个</a>
			</div>
			<div class="frm mb5">
				<label class="lb-chk">
					<input type="checkbox" name="remember" id="remember" value="1" />下次自动登录
				</label>
			</div>
			<div class="frm">
				<button type="button" value="" class="btn-login" id="login-submit" >登&nbsp;录</button>
			</div>
		</form>
	</div>


	<div class="footer">
		<p>&copy; <?php echo date('Y'); ?> nice-php </p>
	</div>
</div>

<script src="assets/js/jquery-2.1.4.min.js"></script>
<script type="text/javascript">

(function($){
	var _height = $(window).height();
	var _top    = Math.ceil((_height - 301) / 2);

	$('#login-form').css('top', _top);
	$("#login-form").delegate("#vcode-refresh","click",function(){
		$('#vcode-img').attr('src', 'index.php?mod=index&action=vcode&t='+Math.random());
	});

	$(window).keydown(function(e){
		if(e.keyCode==13){
   			$('#login-submit').trigger('click'); 
		}
	});


	$("#login-form").delegate("#login-submit","click",function(){
		var username  = $('#username').val();
		var userpass  = $('#userpass').val();
		var vcode     = $('#vcode').val();
		var remember  = $('#remember').is(':checked') ? 1 : 0;
		var timestamp = Date.parse(new Date())/1000;

		if(!username){
			alert('请填写帐号！');
			$('#username').focus();
			return false;
		}

		if(!userpass){
			alert('请填写密码！');
			$('#userpass').focus();
			return false;
		}		

		$.ajax({
			type:"POST",
			url: "index.php?mod=index&action=dologin",
			data:{username: username, userpass: userpass, vcode: vcode, remember: remember, client_time: timestamp},
			dataType: "json",
			success:  function(rep){
				var code       = rep && rep.code ? rep.code : 0;
				var need_vcode = rep && rep.need_vcode ? rep.need_vcode : 0;
				var message    = rep && rep.message ? rep.message : 'system error';

				if(code==200){
					window.location.href = './';
				}else{
					if(need_vcode){
						$('#vcode-div').show();
					}
					alert(message);
				}
			},
			error:    function(){
				alert('system error');
			}
		});
	});

 })(window.jQuery);

</script>

</body>
</html>