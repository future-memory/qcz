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
      <li role="presentation"><a href="index.php?mod=promotion">活动列表 </a></li>
      <li role="presentation" class="active"><a href="#"><?php echo $info ? '编辑' : '添加'; ?>活动</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1" class="form-horizontal" action="" name="form" data-return-url="index.php?mod=promotion&action=index">

      <input type="hidden" name="id" value="<?php echo $id; ?>">


      <div class="form-group">
        <label for="" class="col-sm-2 control-label">抽奖活动</label>
        <div class="col-sm-6">
          <select class="form-control width305" name="lottery_id" id="lottery-id">
            <option value="">请选择</option>
            <?php foreach ($lottery_list as $lottery) { ?>
              <option value="<?php echo $lottery['id']; ?>" ><?php echo $lottery['name']; ?></option>
            <?php } ?>
          </select>
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

var request;
var loading = false;
$("#_submit-btn").click(function() {
  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }

 var lottery_id = jQuery('#lottery-id').val();
  if(!lottery_id){
    alert('请选择抽奖活动！');
    jQuery('#lottery-id').focus();
    return false;
  }

  loading = true;

  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=promotion&action=update");
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