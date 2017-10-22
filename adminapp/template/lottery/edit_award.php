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
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_activity">添加活动</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation" class="active"><a href="#"><?php echo $id ? '编辑' : '添加'; ?>奖品</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1"  class="form-horizontal" action="" name="form" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?=$id?>">

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">奖品名称</label>
        <div class="col-sm-6">
          <input class="form-control width305" type="text" name="name"  value="<?php echo isset($info['name']) ? $info['name'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">奖品类型</label>
        <div class="col-sm-6">
          <select name="type" class="form-control width305">
            <?php foreach ($award_types as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo isset($info['type']) && $key==$info['type'] ? ' selected ' : ''; ?>>
                <?php echo $value; ?>
              </option>
            <?php endforeach ?>
          </select>
          <p class="bg-success width305">
            <span class="help-block">M码等码、券类请选择“虚拟奖品”</span>
          </p>    
        </div>
      </div>

      <div class="form-group mbval" style="<?php echo isset($info['type']) && $info['type']==1 ? '' : 'display:none'; ?>">
        <label for="" class="col-sm-2 control-label">煤球数</label>
        <div class="col-sm-6">         
          <input class="form-control width305" type="text" name="val"  value="<?php echo isset($info['val']) ? $info['val'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">上传图片</label>
        <div class="col-sm-6">
          <input class="form-control width305" name="pic" value="" type="file" >
          <p class="bg-success width305">
            <span class="help-block">建议图片尺寸：400*400 <?php echo isset($info['pic']) && $info['pic'] ? '，上传将覆盖<a href="'.HelperUtils::get_pic_url($info["pic"], "app").'" target="_blank">原有图片</a>' : ''; ?></span>
          </p> 
        </div>
      </div>

      <div class="form-group vawards" style="<?php echo isset($info['type']) && $info['type']==2 ? '' : 'display:none'; ?>">
        <label for="" class="col-sm-2 control-label">消息文案</label>
        <div class="col-sm-6">         
          <textarea rows="10" ondblclick="textareasize(this, 1)" onkeyup="textareasize(this, 0)" name="message" id="message" cols="50"><?php echo isset($info['message']) ? $info['message'] : ''; ?></textarea>          
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

jQuery("select[name='type']").change(function(){
  var val = jQuery(this).val();
  if(val==1){
    jQuery('.mbval').show();
  }else{
    jQuery('.mbval').hide();
  }

  if(val==2){
    jQuery('.vawards').show();
  }else{
    jQuery('.vawards').hide();
  }

});

var request;
var loading = false;
$("#_submit-btn").click(function() {
  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }

  var name = jQuery('input[name="name"]').val();
  if(!name){
    alert('请填写奖品名称！');
    return false;
  }

    var type = jQuery("select[name='type']").val();
    if(1==type){
      var mbval = jQuery("input[name='val']").val();
      if(parseInt(mbval)<1){
        alert('请填写煤球值！');
        return false;
      }
      if(!confirm('煤球类型的奖品，中奖后将直接发放，确定要提交吗？')){
        return false;
      }
    }


  loading = true;

  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=lottery&action=update_award");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {
        submit_confirm(res.message, 'index.php?mod=lottery&action=award_list', 1);
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