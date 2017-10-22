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
    <form id="form1" class="form-horizontal" action="index.php?mod=lottery&action=update_award_probability" name="form" data-return-url="index.php?mod=lottery&action=activity_award_list&id=<?php echo $actid; ?>">
      <input type="hidden" name="id" value="<?=$id?>">

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">活动</label>
        <div class="col-sm-6">
          <?php echo $activity_info['name']; ?>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">奖品</label>
        <div class="col-sm-6">
          <?php echo $award_info['name']; ?>  
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">中奖概率</label>
        <div class="col-sm-6">
          <input class="form-control width305" name="probability" value="<?php echo $info['probability']; ?>" type="text" class="txt" />
          <p class="bg-success width305">
            <span class="help-block">例：中奖几率为1/10请填10，1/100则填100，必中则填1</span>
          </p> 
        </div>
      </div>


<?php if($info['type']==2){ ?>
      <div class="form-group">
        <label for="" class="col-sm-2 control-label">每分钟发放量</label>
        <div class="col-sm-6">
          <input class="form-control width305" name="minute_rate" value="<?php echo $info['minute_rate']; ?>" type="text" class="txt" />
          <p class="bg-success width305">
            <span class="help-block">用于控制奖品发放量，请确保此项填写正确！</span>
          </p> 
        </div>
      </div>
<?php } ?>

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

//选择奖品时
var type = parseInt("<?php echo $info['type']; ?>");

var request;
var loading = false;
$("#_submit-btn").click(function() {
  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }

  var probability = jQuery('input[name="probability"]').val();
  if(!probability){
    alert('请填写中奖概率！');
    jQuery('input[name="probability"]').focus();
    return false;
  }


  if(type==2){
    var minute_rate = jQuery("input[name='minute_rate']").val();
    if(!parseInt(minute_rate) || parseInt(minute_rate)<1){
      if(!confirm('每分钟发放量为0可能会被刷奖！！确定提交吗？')){
        jQuery("input[name='minute_rate']").focus();
        return false;
      }
    }   
  }
  
  loading = true;

  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=lottery&action=update_award_probability");
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