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
      <li role="presentation" class="active"><a href="#">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p>
  <div>
    <table class="table table-striped fixed">
      <thead>      
        <tr>
          <th>奖品名称</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['name']; ?></td>
          <td>
            <a href="index.php?mod=lottery&action=edit_award&id=<?php echo $item['id']; ?>">修改</a>   
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