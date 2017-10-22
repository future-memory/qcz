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
      <li role="presentation"><a href="index.php?mod=promotion">活动列表</a></li>
      <li role="presentation"><a href="index.php?mod=promotion&action=edit">添加活动</a></li>
      <li role="presentation" class="active"><a href="#">IMEI管理</a></li>
    </ul>
  </div>


  <p>
  <div class="row" style="height:40px;">
    <span>活动：<?php echo $lottery['name']; ?></span>
  </div>
    
  <div>
    <table class="table table-striped fixed">
      <thead>      
        <tr>
          <th>IMEI</th>
          <th>已使用</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['imei']; ?></td>
          <td><?php echo $item['uid'] ? '是' : '否'; ?></td>
          <td>
            <a href="#" class="del" data-pid="<?php echo $item['pid']; ?>" data-imei="<?php echo $item['imei']; ?>">删除</a>
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
$('.del').click(function(){
  if(!confirm('确定要删除吗？')){
    return;
  }
  var _parent = $(this).parent().parent();
  var pid     = $(this).attr('data-pid');
  var imei    = $(this).attr('data-imei');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=promotion&action=delimei&wants_json=true",
    data: {pid: pid, imei: imei},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('操作成功！');
          _parent.remove();
      }else{
        submit_alert(res.message);
      }
    }
  });
});
</script>

    </body>
</html>