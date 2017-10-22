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
      <li role="presentation"><a href="index.php?mod=lottery&action=award_queue&id=<?php echo $activity_info['id']; ?>">奖品队列</a></li>
      <li role="presentation" class="active"><a href="#">修改队列奖品发放时间</a></li>

      <li role="presentation"><a href="index.php?mod=lottery&action=edit_activity">添加活动</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1" class="form-horizontal" action="index.php?mod=lottery&action=change_send_time" name="form" data-return-url="index.php?mod=lottery&action=award_queue&id=<?php echo $activity_info['id']; ?>">
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
        <label for="" class="col-sm-2 control-label">发放时间</label>
        <div class="col-sm-6">
          <input name="send_time" value="<?php echo is_numeric($info['send_time']) ? date('Y-m-d H:i:s', $info['send_time']) : $info['send_time']; ?>" type="text" class="form-control width305" />

        </div>
      </div>


      <div class="form-group">
        <span class="col-sm-2"></span>
        <div class="col-sm-20">
          <button type="button" class="btn btn-primary" name="editsubmit" id="submit-btn">提交</button>  
        </div>
      </div>
    </form>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>

    </body>
</html>