<?php include(APP_ROOT . '/template/common/header.php'); ?>
<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>
<link rel="stylesheet" href="assets/kd/themes/default/default.css" />
<script charset="utf-8" src="assets/kd/kindeditor-all-min.js"></script>
<script charset="utf-8" src="assets/kd/lang/zh-CN.js"></script>
<style type="text/css">
  .row {
    margin-left: 0;
    margin-right: 0;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }

  #search-form span {
    margin-left: 20px;
  }

  #search-btn {
    margin-left: 20px;
  }
</style>
<div class="container-fluid">
  <p></p>
  <div>
    <ul class="nav nav-tabs">
      <?php foreach ($this->tabs as $action => $action_name): ?>
        <?php if ($this->current_action == $action): ?>
      <li role="presentation" class="active"><a href="#"><?=$action_name?></a></li>
        <?php else: ?>
      <li role="presentation"><a href="index.php?mod=shop&action=<?=$action?>"><?=$action_name?></a></li>
        <?php endif ?>
      <?php endforeach ?>
    </ul>
  </div>
  <p>
  <div>订单号: <?=$id?></div>
  <p>
  <form name="form" method="post" class="form-horizontal" action="" id="audit-form">
    <input type="hidden" name="id" value="<?=$id?>" />
    <input type="hidden" name="url" value="<?=urlencode($url)?>" />
    <div id="audit-tbl">
      <div>操作</div>

      <div class="form-group">
        <label class="col-sm-1 control-label">快递公司</label>
        <div class="col-sm-3">
          <select class="form-control" name="deliver" id="deliver">
            <option value="">请选择</option>
            <option value="顺丰" <?=isset($info['deliver']) && '顺丰'==$info['deliver'] ? 'selected' : ''; ?>>顺丰</option>
            <option value="EMS" <?=isset($info['deliver']) && 'EMS'==$info['deliver'] ? 'selected' : ''; ?>>EMS</option>
            <option value="邮政" <?=isset($info['deliver']) && '邮政'==$info['deliver'] ? 'selected' : ''; ?>>邮政</option>
            <option value="韵达" <?=isset($info['deliver']) && '韵达'==$info['deliver'] ? 'selected' : ''; ?>>韵达</option>
            <option value="圆通" <?=isset($info['deliver']) && '圆通'==$info['deliver'] ? 'selected' : ''; ?>>圆通</option>
            <option value="申通" <?=isset($info['deliver']) && '申通'==$info['deliver'] ? 'selected' : ''; ?>>申通</option>
            <option value="宅急送" <?=isset($info['deliver']) && '宅急送'==$info['deliver'] ? 'selected' : ''; ?>>宅急送</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">快递单号</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="delivery_sn"  value="<?=isset($info['delivery_sn']) ? $info['delivery_sn'] : ''?>">
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">是否发送系统消息</label>
        <div class="col-sm-7">
          <label class="radio-inline">
            <input type="radio" name="send" value="1"> 是
          </label>
          <label class="radio-inline">
            <input type="radio" name="send" value="0"> 否
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">系统消息内容</label>
        <div class="col-sm-7">
          <textarea class="form-control" cols="50" rows="6" name="message" id="message" rows="6">您的订单（<a href="">点击查看</a>）已经发货，快递公司为{deliver}，快递单号为{sn}，请您留意后续动态。</textarea>
          <p class="bg-success">
            <span class="help-block">{deliver}会被替换为快递公司名，{sn}会被替换为所填写的快递单号</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <span class="col-sm-1"></span>
        <div class="col-sm-7">
          <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>  
        </div>
      </div>

    </div>
  </form>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
var editor;
KindEditor.ready(function(K) {
  editor1 = K.create('textarea[name="message"]', {
    resizeType : 1,
    allowPreviewEmoticons : false,
    allowImageUpload : true,
    afterBlur:function(){
        this.sync();
    },          
    items : [
      'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
      'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
      'insertunorderedlist', '|', 'link']
  });
});
var request;
var loading = false;
$("#_submit-btn").click(function() {
  var deliver = $("select[name='deliver']").val();
  if(!deliver){
    alert('请选择物流！');
    $("select[name='deliver']").focus();
    return false;
  }
    
  var delivery_sn = $("input[name='delivery_sn']").val();
  if(!delivery_sn){
    alert('请填写快递单号！');
    $("input[name='delivery_sn']").focus();
    return false;
  }
  var send = $('input[name="send"]:checked').val();
  if(typeof(send)=='undefined'){
    alert('请选择是否发送系统消息！');
    return false;
  }

  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }
  loading = true;
  var formElement = document.querySelector("#audit-form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=shop&action=delivery_order");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      console.log(request.responseText);
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {

        submit_confirm(res.message, res.returl, 1);
      } else {
        submit_alert(res.message);
      }
    } else {
      alert("Problem retrieving XML data");
    }
    loading = false;
  }
}

function post_data(url, data, _returl = '') {
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code == 200) {
        console.log(res);
        if (res.returl) {
          console.log(res.returl);
          submit_confirm(res.message, res.returl, 1);
        } else {
          submit_alert(res.message);
        }
      }else{
        // code 402 502
        if (res.returl) {
            submit_hint(res.message, res.returl, res.hint);
        } else {
            submit_alert(res.message);
        }     
      }
    }
  });
}
</script>
    </body>
</html>