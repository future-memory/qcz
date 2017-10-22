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
 .form-control,.bg-success {
    width: 305px;
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
  <p></p>
  <div>
    <form id="form1"  class="form-horizontal" action="" name="form" enctype="multipart/form-data">

      <input type="hidden" name="id" value="<?=isset($info['id']) ? $info['id'] : ''?>">

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">类型名称</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="name"  value="<?=isset($info['name']) ? $info['name'] : ''?>">
        </div>
      </div>

      <div class="form-group">
        <span class="col-sm-1"></span>
        <div class="col-sm-10">
          <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>  
        </div>
      </div>

    </form>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

var request;
var loading = false;
$("#_submit-btn").click(function() {
  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }
  loading = true;
  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=shop&action=update_goods_type");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      console.log(request.responseText);
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {

        submit_confirm(res.message, 'index.php?mod=shop&action=goods_type', 1);
      } else {
        submit_alert(res.message);
      }
    } else {
      alert("Problem retrieving XML data");
    }
    loading = false;
  }
}
</script>
    </body>
</html>