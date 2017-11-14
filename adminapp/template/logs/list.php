<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
    .table>tbody>tr>td {
        border-top: none;
    }
    .table>thead>tr>th {
        border-bottom: none;
    }
    .row {
      margin-left: 0;
      margin-right: 0;
    }
</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <form action="index.php?mod=logs" method="post" name="form" enctype="multipart/form-data">
      <div style="text-align: right">
        <select name="month" id="month" style="min-width: 150px">
        <?php foreach ($months as $key => $month) {?>
            <option value="<?php echo $month; ?>" <?php echo $selected === $month ? 'selected' : ''; ?> >
            <?php echo $month; ?>
            </option>
        <?php } ?>
        </select>
      </div>
    </form>
  </div>
  <div class="row">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th width="100">操作者</th>
          <th width="100">角色</th>
          <th width="30">IP地址</th>
          <th width="100">时间</th>
          <th>动作</th>
          <th>其它</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($logs as $key => $log) {
          $value = explode("\t", $log);
          if(empty($log[1])) {
            continue;
          }
        ?>
        <tr>
          <td><?php echo $value[2]; ?></td>
          <td><?php echo in_array($value[2], $founders) ? 'Master' : (isset($roles[(int)$value[3]]) ? $roles[(int)$value[3]] : ''); ?></td>
          <td><?php echo $value[4]; ?></td>
          <td><?php echo $value[1] ? date('Y-m-d H:i:s', (int)$value[1]) : '-'; ?></td>
          <td><?php echo $value[5]; ?></td>
          <td><?php echo $value[6]; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <?php echo $pager; ?>

</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
$('#month').change(function() {   
    location.href = 'index.php?mod=logs&action=index&month=' + $(this).val();
});
</script>
    </body>
</html>