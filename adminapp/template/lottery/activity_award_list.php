<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  .row {
    margin-left: 0;
    margin-right: 0;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }
</style>
<div class="container-fluid">
  <p></p>
  <div>
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=lottery">活动列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_activity">添加活动</a></li>
      <li role="presentation" class="active"><a href="#">已分配奖品</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p>
  <div>

  <div class="row" style="min-width:800px;">
      <div style="float:left;display:inline-block;">
        活动：<?php echo $info['name']; ?> 
        <a href="index.php?mod=lottery&action=award_distribute&id=<?php echo $id; ?>">
          <button type="button" class="btn btn-default btn-sm">
            <span class="glyphicon glyphicon-cog"></span> 分配奖品
          </button>
        </a>  
      </div>

<div style="float:right;display:inline-block;">
</div>

  </div>

    <table class="table table-striped fixed">
      <thead>
        <tr>
          <th width="60px;">奖品id</th>
          <th>奖品名称</th>
          <th width="100px;">中奖概率</th>
          <th width="100px;">类型</th>
          <th width="130px;">每分钟发放量</th>
          <th width="80px;">奖品数量</th>
          <th width="80px;">奖品剩余</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['awardid']; ?></td>
          <td><?php echo $item['award_name']; ?></td>
          <td><?php echo '1/'.$item['probability']; ?></td>
          <td><?php echo $item['type']==2 ? '库存' : '队列'; ?></td>
          <td><?php echo $item['type']==2 ? $item['minute_rate'] : '-'; ?></td>
          <td><?php echo $item['count']; ?></td>
          <td><?php echo $item['type']==2 ? $item['left'] : '<a href="index.php?mod=lottery&action=award_queue&id='.$id.'&awardid='.$item['awardid'].'">'.$item['qleft'].'</a>'; ?></td>
          <td data-awardid="<?php echo $item['awardid']; ?>" data-actid="<?php echo $item['activityid']; ?>">
            <a href="index.php?mod=lottery&action=edit_award_probability&id=<?php echo $item['id']; ?>&activityid=<?php echo $id; ?>">修改</a>&nbsp;&nbsp;
            <a class="del" <?php echo $item['status']!=1 ? 'style="display:none;"' : ''; ?> href="#">移除奖品</a>
            <a class="rec" style="color:gray; <?php echo $item['status']==1 ? 'display:none;' : ''; ?>" href="#">恢复奖品</a>

            <a class="def" <?php echo $item['is_default']!=1 ? '' : 'style="display:none"'; ?> href="#">设为默认</a>
            <a class="und" style="color:gray; <?php echo $item['is_default']==1 ? '' : 'display:none'; ?>" href="#">取消默认</a>

          </td>
        </tr>            
      <?php } ?>
      </tbody>
    </table>
  </div>
  
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>

<script type="text/javascript">


$('.del').click(function(){
  if(!confirm('移除将删除奖品对应的队列！！确定要删除吗？')){
    return;
  }
  var _this = $(this);
  var _parent = $(this).parent();
  var activityid = _parent.attr('data-actid');
  var awardid    = _parent.attr('data-awardid');
  $.ajax({
    type: "POST",
    url:  "index.php?mod=lottery&action=delete_activity_award&wants_json=true",
    data: {activityid: activityid, awardid: awardid},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('删除成功！');
          _this.hide();
          _parent.children('.rec').show();
      }else{
        submit_alert(res.message);
      }
    }
  });
});

$('.rec').click(function(){
  if(!confirm('确定要恢复吗？')){
    return;
  }
  var _this = $(this);
  var _parent = $(this).parent();
  var activityid = _parent.attr('data-actid');
  var awardid    = _parent.attr('data-awardid');
  $.ajax({
    type: "POST",
    url:  "index.php?mod=lottery&action=recovery_activity_award&wants_json=true",
    data: {activityid: activityid, awardid: awardid},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('恢复成功！');
          _this.hide();
          _parent.children('.del').show();
      }else{
        submit_alert(res.message);
      }
    }
  });
});

$('.def').click(function(){
  if(!confirm('确定要设置为默认奖品吗？')){
    return;
  }
  var _this = $(this);
  var _parent = $(this).parent();
  var activityid = _parent.attr('data-actid');
  var awardid    = _parent.attr('data-awardid');
  $.ajax({
    type: "POST",
    url:  "index.php?mod=lottery&action=set_activity_award_default&wants_json=true",
    data: {activityid: activityid, awardid: awardid, val: 1},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('设置成功！');
          _this.hide();
          _parent.children('.und').show();
      }else{
        submit_alert(res.message);
      }
    }
  });
});

$('.und').click(function(){
  if(!confirm('确定要取消默认奖品吗？')){
    return;
  }
  var _this = $(this);
  var _parent = $(this).parent();
  var activityid = _parent.attr('data-actid');
  var awardid    = _parent.attr('data-awardid');
  $.ajax({
    type: "POST",
    url:  "index.php?mod=lottery&action=set_activity_award_default&wants_json=true",
    data: {activityid: activityid, awardid: awardid, val: 0},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('取消成功！');
          _this.hide();
          _parent.children('.def').show();
      }else{
        submit_alert(res.message);
      }
    }
  });
});

</script>

    </body>
</html>