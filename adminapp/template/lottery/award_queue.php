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
      <li role="presentation" class="active"><a href="#">奖品队列</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p>
  <div>

<form method="get" action="index.php">
  <div class="row" style="min-width:800px;height:40px;">
    <span>活动：<?php echo $info['name']; ?></span>

    <span style="position:absolute;right:50px;">
        <select name="awardid" class="form-control" style="width:160px;display: inline-block;">
          <option value="">全部</option>
          <?php foreach($awards as $award){ ?>
            <option value="<?php echo $award['id']; ?>" <?php echo $awardid==$award['id'] ? 'selected' : ''; ?>><?php echo $award['name']; ?></option>
          <?php } ?>
        </select>

        <input type="hidden" name="mod" value="lottery" />
        <input type="hidden" name="action" value="award_queue" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <button type="submit" class="btn btn-primary" name="submit">查询</button>
    </span>
  </div>
</form>

    <table class="table table-striped fixed">
      <thead>
        <tr>
          <th>id</th>
          <th>奖品名称</th>
          <th>奖品状态</th>
          <th>发出时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['id']; ?></td>
          <td><?php echo $item['award_name']; ?></td>
          <td><?php echo $item['flag'] ? '已发出' : '未发出'; ?></td>
          <td><?php echo $item['send_time'] ? date('Y-m-d H:i:s', $item['send_time']) : ''; ?></td>
          <td>
            <?php if(!$item['flag']){ ?>
              <a data-id="<?php echo $item['id']; ?>" href="#" class="send">置为已发送</a>&nbsp;&nbsp;
              <a href="index.php?mod=lottery&action=edit_queue&id=<?php echo $item['id']; ?>">修改发出时间</a>&nbsp;&nbsp;
            <?php }else{echo '-';} ?>
          </td>
        </tr>            
      <?php } ?>
      </tbody>
    </table>
  </div>

  <?php echo $pager; ?>
  
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>

<script type="text/javascript">
$('.send').click(function(){
  if(!confirm('确定要置为已发送吗？')){
    return;
  }
  var _parent = $(this).parent();
  var id      = $(this).attr('data-id');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=lottery&action=change_flag&wants_json=true",
    data: {id: id, val: 0},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('操作成功！');
          _parent.html('-');
      }else{
        submit_alert(res.message);
      }
    }
  });
});
</script>

    </body>
</html>