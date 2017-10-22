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
      <li role="presentation"><a href="index.php?mod=lottery&action=activity_award_list&id=<?php echo $actid; ?>">已分配奖品</a></li>
      <li role="presentation" class="active"><a href="#">编辑已分配奖品概率</a></li>

      <li role="presentation"><a href="index.php?mod=lottery&action=edit_activity">添加活动</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1" class="form-horizontal" action="" name="form" data-return-url="index.php?mod=lottery&action=activity_award_list&id=<?php echo $id; ?>"  enctype="multipart/form-data">

      <input type="hidden" name="id" value="<?php echo $id; ?>">

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">活动</label>
        <div class="col-sm-6">
          <?php echo $info['name']; ?>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">奖品</label>
        <div class="col-sm-6">
          <select class="form-control width305" name="awardid" id="awardid">
            <option value="">请选择</option>
            <?php foreach ($award_list as $award) { ?>
              <option value="<?php echo $award['id']; ?>" data-type="<?php echo $award['type']; ?>"><?php echo $award['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">分配数量</label>
        <div class="col-sm-6">
          <input name="count" value="" type="text" class="form-control width305" />
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">中奖概率</label>
        <div class="col-sm-6">
          <input class="form-control width305" name="probability" value="" type="text" />
          <p class="bg-success width305">
            <span class="help-block">例：中奖几率为1/10请填10，1/100则填100，必中则填1</span>
          </p> 
        </div>
      </div>


      <div class="form-group">
        <label for="" class="col-sm-2 control-label">开始时间</label>
        <div class="col-sm-6">
          <input name="start_time" id="start-time" class="form-control width305" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'#F{$dp.$D(\'end-time\')}'})" value="<?php echo is_numeric($info['start_time']) ? date('Y-m-d H:i:s', $info['start_time']) : $info['start_time']; ?>" type="text" />
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">结束时间</label>
        <div class="col-sm-6">
          <input name="end_time" id="end-time" class="form-control width305" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:'#F{$dp.$D(\'start-time\')}'})" value="<?php echo is_numeric($info['end_time']) ? date('Y-m-d H:i:s', $info['end_time']) : $info['end_time']; ?>" type="text" />
        </div>
      </div>

      <div class="form-group">
          <label for="" class="col-sm-2 control-label">分配类型</label>
          <div class="col-sm-6">
            <input class="select-class-type" name="type" value="1" type="radio" id="enable1">
            <label for="enable1">队列</label>
            <input class="select-class-type" name="type" value="2" type="radio" id="enable0">
            <label for="enable0">库存</label>

            <p class="bg-success width305">
              <span class="help-block">
                队列：奖品会生成队列，按时间进行抽取<br>
                库存：直接减库存，通过每分钟发放量控制频率<br>
                为防止奖品被刷，目前仅支持煤球使用库存方式
              </span>
            </p> 

          </div>
      </div>

      <div class="form-group minute_rate" style="display:none">
        <label for="" class="col-sm-2 control-label">每分钟发放量</label>
        <div class="col-sm-6">
          <input class="form-control width305" name="minute_rate" value="" type="text" />
          <p class="bg-success width305">
            <span class="help-block">用于控制奖品发放量，请确保此项填写正确！</span>
          </p> 
        </div>
      </div>

      <div class="form-group vawards" style="display:none">
        <label for="" class="col-sm-2 control-label">奖品列表</label>
        <div class="col-sm-6">
          <input name="vawards" value="" type="file" class="form-control width305" />
          <p class="bg-success width305">
            <span class="help-block">用于控制奖品发放量，请确保此项填写正确！</span>
          </p> 
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

<script type="text/JavaScript">

jQuery("input[name='type']").click(function(){
  var val = jQuery(this).val();
  if(val==2){
    jQuery('.minute_rate').show();
  }else{
    jQuery('.minute_rate').hide();
  }
});

//选择奖品时
var award_type = 0;
jQuery("#awardid").change(function(){
  award_type = jQuery("#awardid").find("option:selected").attr('data-type');
  if(award_type==1){
    jQuery('input[name="type"]').eq(0).attr("checked",false);
    jQuery('input[name="type"]').eq(1).attr("checked",true);
    jQuery('.minute_rate').show();
  }else{
    jQuery('input[name="type"]').eq(1).attr("checked",false);
    jQuery('input[name="type"]').eq(0).attr("checked",true);
    jQuery('.minute_rate').hide();
  }

  if(award_type==2){
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

 var awardid = jQuery('#awardid').val();
  if(!awardid){
    alert('请选择奖品！');
    jQuery('#awardid').focus();
    return false;
  }

  var count = jQuery('input[name="count"]').val();
  if(!count){
    alert('请填写数量！');
    jQuery('input[name="count"]').focus();
    return false;
  }

  var probability = jQuery('input[name="probability"]').val();
  if(!probability){
    alert('请填写中奖概率！');
    jQuery('input[name="probability"]').focus();
    return false;
  }

  var type = jQuery('input[name="type"]:checked').val();
  if(typeof(type)=='undefined'){
    alert('请选择分配类型！');
    return false;
  }

  if(type==2){
    // if(award_type!=1){
    //   alert('非煤球奖品请使用队列方式！');
    //   return;
    // }
    var minute_rate = jQuery("input[name='minute_rate']").val();
    if(!parseInt(minute_rate) || parseInt(minute_rate)<1){
      if(!confirm('每分钟发放量为0可能会被刷奖！！确定提交吗？')){
        jQuery("input[name='minute_rate']").focus();
        return false;
      }
    }   
  }else{
    if(count<0){
      alert('队列模式请勿提交负数');
      return false;
    }
  }
  
  loading = true;

  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=lottery&action=distribute");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {
        var ret_url = $('#form1').attr('data-return-url');
        submit_confirm(res.message, ret_url, 1);
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