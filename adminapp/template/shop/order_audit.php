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
        <label class="col-sm-1 control-label">状态</label>
        <div class="col-sm-7">
          <label class="radio-inline">
            <input type="radio" name="status" value="1"> 通过
          </label>
          <label class="radio-inline">
            <input type="radio" name="status" value="2"> 不通过
          </label>
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
          <textarea class="form-control" cols="50" rows="6" name="message" id="message" rows="6">您的订单（<a href="">点击查看</a>）{audit_result}审核。</textarea>
          <p class="bg-success">
            <span class="help-block">{audit_result}会被替换为已通过或未通过</span>
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
  var status = $('input[name="status"]:checked').val();
  if(typeof(status)=='undefined'){
    alert('请选择审核结果！');
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
  request.open("POST", "index.php?mod=shop&action=audit_order");
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
        if (res.returl) {
          submit_confirm(res.message, res.returl, 1);
        } else if(_returl){
          submit_confirm(res.message, _returl, 1);
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