<?php include(APP_ROOT . '/template/common/header.php'); ?>
<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>
<style type="text/css">
  .row {
    margin-left: 0;
    margin-right: 0;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }

  #search-form span {
    margin-left: 20px;
  }

  #search-btn {
    margin-left: 20px;
  }
</style>
<div class="container-fluid">
  <p></p>
  <div>
    <ul class="nav nav-tabs">
      <?php foreach ($this->tabs as $action => $action_name): ?>
        <?php if ($this->current_action == $action): ?>
      <li role="presentation" class="active"><a href="#"><?=$action_name?></a></li>
        <?php else: ?>
      <li role="presentation"><a href="index.php?mod=shop&action=<?=$action?>"><?=$action_name?></a></li>
        <?php endif ?>
      <?php endforeach ?>
    </ul>
  </div>
  <p>
  <div>
    <table class="table table-striped fixed">
      <thead>
        <col width="20px"></col>
        <col width="120px"></col>
        <col width="80px"></col>
        <col width="80px"></col>
        <col width="80px"></col>
        <col width="50px"></col>
        <col width="120px"></col>
        <tr>
          <th>id</th>
          <th>商品名称</th>
          <th>商品类型</th>
          <th>价格</th>
          <th>货存量</th>
          <th>是否上架</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item): ?>
        <tr>
          <td><?php echo $item['id']; ?></td>
          <td><?php echo $item['name']; ?></td>
          <td><?php echo isset($types[$item['type']]) ? $types[$item['type']]['name'] : '-' ; ?></td>
          <td><?php echo $item['price']; ?></td>
          <td><?php echo $item['count']; ?></td>
          <td><?php echo $item['is_online'] ? '是' : '否'; ?></td>
          <td>
            <a href="index.php?mod=shop&action=edit_goods&id=<?php echo $item['id']; ?>">修改</a>&nbsp;&nbsp;
            <a href="index.php?mod=shop&action=edit_goods_count&id=<?php echo $item['id']; ?>">更新库存</a>&nbsp;&nbsp;
          <?php if($item['is_online']){ ?>
            <a onclick="online(<?=$item['id']?>, 0)" href="javascript:;">下架</a>&nbsp;&nbsp;
          <?php }else{ ?>
            <a onclick="online(<?=$item['id']?>, 1)" href="javascript:;">上架</a>&nbsp;&nbsp;
          <?php } ?>
          </td>
        </tr>       
      <?php endforeach ?>
      </tbody>
    </table>
    <?=$pager?>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

function online(id, val) {
  var str = '上架';
  if (val == 0) {
    str = '下架';
  }
  if (confirm('是否' + str)) {
    post_data('index.php?mod=shop&action=online&wants_json=true', 'id=' + id + '&val=' + val, window.location.href);
  } 
}

function post_data(url, data, _returl = '') {
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code == 200) {
        if (_returl) {
          submit_confirm(res.message, _returl, 1);
        } else {
          submit_alert(res.message);
        }
      }else{
        // code 402 502
        if (res.returl) {
            submit_hint(res.message, res.returl, res.hint);
        } else {
            submit_alert(res.message);
        }     
      }
    }
  });
}
</script>
    </body>
</html>