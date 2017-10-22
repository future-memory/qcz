<?php include(APP_ROOT . '/template/common/header.php'); ?>

<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>

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
      <li role="presentation" class="active"><a href="#">获奖列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=award_list">奖品列表</a></li>
      <li role="presentation"><a href="index.php?mod=lottery&action=edit_award">添加奖品</a></li>
    </ul>
  </div>
  <p>

<form name="searchform" method="get" action="index.php" id="searchform">
  <div class="row" style="height:40px;">

      <span>活动：<?php echo $info['name']; ?></span>

      <span style="position:absolute;right:50px;">
        <span>
          奖品：
          <select name="awardid" class="form-control" style="width:160px;display: inline-block;">
            <option value="">全部</option>
            <?php foreach($awards as $award){ ?>
              <option value="<?php echo $award['id']; ?>" <?php echo $awardid==$award['id'] ? 'selected' : ''; ?>><?php echo $award['name']; ?></option>
            <?php } ?>
          </select>
        </span>

        <span>
          日期：
          <input type="text" placeholder="开始时间" id="start-time" style="width:160px;display: inline-block;" class="form-control" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'#F{$dp.$D(\'end-time\')}'})" value="<?php echo is_numeric($start_time) ? date('Y-m-d H:i:s', $start_time) : $start_time; ?>" name="start_time" > -
          <input type="text" placeholder="结束时间" id="end-time" style="width:160px;display: inline-block;" class="form-control" onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:'#F{$dp.$D(\'start-time\')}'})" value="<?php echo is_numeric($end_time) ? date('Y-m-d H:i:s', $end_time) : $end_time; ?>" name="end_time" >        
        </span>

        <input type="hidden" name="mod" value="lottery" />
        <input type="hidden" name="action" value="win_list" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <button type="submit" class="btn btn-primary" name="submit">查询</button>

        <a id="export" href="javascript:;" onclick="return false;">导出</a>

      </span>
  </div>
</form>


  <div>
    <table class="table table-striped fixed">
      <thead><tr><th width="100px">UID</th><th width="150px">用户名</th><th width="120px">IP</th><th width="150px">奖品</th><th width="150px">中奖时间</th><th width="90px">已发放奖品</th><th>地址信息(姓名|电话|地址)</th></tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['uid']; ?></td>
          <td><?php echo $item['username']; ?></td>
          <td><?php echo $item['ip']; ?></td>
          <td><?php echo $item['award_name']; ?></td>
          <td><?php echo $item['win_time'] ? date('Y-m-d H:i:s', $item['win_time']) : ''; ?></td>
          <td><?php echo $item['sended'] ? '是' : '否'; ?></td>
          <td><?php echo $item['address_info'] ? $item['address_info'] : '-'; ?></td>
        </tr>           
      <?php } ?>
      </tbody>
    </table>
  </div>

  <?php echo $pager; ?>

</div>

<?php include(APP_ROOT . '/template/common/footer.php'); ?>

<script type="text/javascript">
jQuery('#export').click(function(){
  var _url = window.location.href;
  window.location.href = _url + '&action=win_export';
});
</script>

    </body>
</html>