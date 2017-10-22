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
      <li role="presentation" class="active"><a href="#">活动列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_activity">添加活动</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p>
  <div>
    <table class="table table-striped fixed">
      <thead>      
        <tr>
          <th width="60px">id</th>
          <th>活动名称</th>
          <th width="180px">开始时间</th>
          <th width="180px">结束时间</th>
          <th width="60px">状态</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['id']; ?></a></td>
          <td><?php echo $item['name']; ?></td>
          <td><?php echo date('Y-m-d H:i:s', $item['start_time']); ?></td>
          <td><?php echo date('Y-m-d H:i:s', $item['end_time']); ?></td>
          <td><?php echo $item['enable'] ? '开启' : '关闭'; ?></td>
          <td>
            <a href="index.php?mod=lottery&action=edit_activity&id=<?php echo $item['id']; ?>">设置</a>
            <a href="index.php?mod=lottery&action=activity_award_list&id=<?php echo $item['id']; ?>">奖品分配</a>
            <a href="index.php?mod=lottery&action=award_queue&id=<?php echo $item['id']; ?>">奖品队列</a>
            <a href="index.php?mod=lottery&action=win_list&id=<?php echo $item['id']; ?>">获奖列表</a>
          </td>
        </tr>            
      <?php } ?>
      </tbody>
    </table>
  </div>

  <?php echo $pager; ?>
  
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>

    </body>
</html>