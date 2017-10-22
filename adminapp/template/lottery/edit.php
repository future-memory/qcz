<?php include(APP_ROOT . '/template/common/header.php'); ?>
<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>
<style type="text/css">
  .row {
    margin-left: 0;
    margin-right: 0;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }
  .width305{width: 305px;}
</style>
<div class="container-fluid">
  <p></p>
  <div>
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=lottery">活动列表</a></li>
      <li role="presentation" class="active">
        <a href="index.php?mod=lottery&action=edit_activity"><?php echo $id ? '编辑' : '添加'; ?>活动</a>
      </li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1"  class="form-horizontal" action="" name="form" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?=$id?>">

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">活动名称</label>
        <div class="col-sm-6">
          <input class="form-control width305" type="text" name="name"  value="<?php echo isset($info['name']) ? $info['name'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
          <label for="" class="col-sm-2 control-label">是否开启</label>
          <div class="col-sm-6">
            <input class="select-class-type margin-right10" name="enable" value="0" <?php if(!isset($info['enable']) || !$info['enable']){ ?>checked<?php } ?> type="radio" id="enable1">
            <label for="enable1">关闭</label>
            <input class="select-class-type margin-right10" name="enable" value="1" <?php if(isset($info['enable']) && $info['enable']==1){ ?>checked<?php } ?> type="radio" id="enable0">
            <label for="enable0">开启</label>
            <input class="select-class-type margin-right10" name="enable" value="2" <?php if(isset($info['enable']) && $info['enable']==2){ ?>checked<?php } ?> type="radio" id="enable2">
            <label for="enable2">仅服务调用</label>
          </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">初始化抽奖机会</label>
        <div class="col-sm-6">
          <select name="init_chance_type" class="form-control" style="width:120px;display:inline;">
            <?php foreach ($chance_types as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo isset($info['init_chance_type']) && $key==$info['init_chance_type'] ? 'selected' : ''; ?>><?php echo $value; ?></option>
            <?php endforeach ?>
          </select>         
          <input class="form-control" style="width:180px;display:inline;" type="text" name="init_chance"  value="<?php echo isset($info['init_chance']) ? $info['init_chance'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">允许中奖次数</label>
        <div class="col-sm-6">
          <select name="allow_win_type" class="form-control" style="width:120px;display:inline;">
            <?php foreach ($chance_types as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo isset($info['allow_win_type']) && $key==$info['allow_win_type'] ? 'selected' : ''; ?>><?php echo $value; ?></option>
            <?php endforeach ?>
          </select>         
          <input class="form-control" style="width:180px;display:inline;" type="text" name="allow_win"  value="<?php echo isset($info['allow_win']) ? $info['allow_win'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">允许抽奖机会数</label>
        <div class="col-sm-6">
          <select name="max_chance_type" class="form-control" style="width:120px;display:inline;">
            <?php foreach ($chance_types as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo isset($info['max_chance_type']) && $key==$info['max_chance_type'] ? 'selected' : ''; ?>><?php echo $value; ?></option>
            <?php endforeach ?>
          </select>         
          <input class="form-control" style="width:180px;display:inline;" type="text" name="max_chance"  value="<?php echo isset($info['max_chance']) ? $info['max_chance'] : ''; ?>">
        </div>
      </div>

    <?php $info['start_time'] = isset($info['start_time']) ? $info['start_time'] : ''; ?>
    <?php $info['end_time']   = isset($info['end_time']) ? $info['end_time'] : ''; ?>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">开始时间</label>
        <div class="col-sm-6">
          <input name="start_time" id="start-time" class="form-control width305" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'#F{$dp.$D(\'end-time\')}'})" value="<?php echo is_numeric($info['start_time']) ? date('Y-m-d H:i:s', $info['start_time']) : $info['start_time']; ?>" type="text" class="txt"   />
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">结束时间</label>
        <div class="col-sm-6">
          <input name="end_time" id="end-time" class="form-control width305" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:'#F{$dp.$D(\'start-time\')}'})" value="<?php echo is_numeric($info['end_time']) ? date('Y-m-d H:i:s', $info['end_time']) : $info['end_time']; ?>" type="text" class="txt"   />
        </div>
      </div>      

      <div class="form-group">
        <span class="col-sm-2"></span>
        <div class="col-sm-20">
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

  var name = jQuery('input[name="name"]').val();
  if(!name){
    alert('请填写抽奖活动名称！');
    return false;
  } 
  var enable = jQuery('input[name="enable"]:checked').val();
  if(typeof(enable)=='undefined'){
    alert('请选择是否开启！');
    return false;
  }
  var init_chance_type = jQuery('select[name="init_chance_type"]').val();
  if(init_chance_type<1){
    alert('请选择初始化抽奖机会的类型！');
    jQuery('input[name="init_chance_type"]').focus();
    return false;
  }
  var allow_win_type = jQuery('select[name="allow_win_type"]').val();
  if(allow_win_type<1){
    alert('请选择允许中奖次数的类型！');
    jQuery('input[name="allow_win_type"]').focus();
    return false;
  }
  var max_chance_type = jQuery('select[name="max_chance_type"]').val();
  if(max_chance_type<1){
    alert('请选择允许抽奖机会数的类型！');
    jQuery('input[name="max_chance_type"]').focus();
    return false;
  }

  loading = true;

  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=lottery&action=update_activity");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      console.log(request.responseText);
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {

        submit_confirm(res.message, 'index.php?mod=lottery', 1);
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