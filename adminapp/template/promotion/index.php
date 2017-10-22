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
      <li role="presentation"><a href="index.php?mod=promotion&action=edit">添加活动</a></li>
    </ul>
  </div>
  <p>
  <div>
    <table class="table table-striped fixed">
      <thead>      
        <tr>
          <th>id</th>
          <th>活动名称</th>
          <th>当前活动</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $item){ ?>
        <tr>
          <td><?php echo $item['id']; ?></a></td>
          <td><?php echo $item['name']; ?></td>
          <td><?php echo $item['is_current'] ? '是' : '否'; ?></td>
          <td>
            <!--<a href="index.php?mod=promotion&action=edit&id=<?php echo $item['id']; ?>">修改</a>-->
            <a href="index.php?mod=promotion&action=import&id=<?php echo $item['id']; ?>">IMEI导入</a>
            <a href="index.php?mod=promotion&action=imei&id=<?php echo $item['id']; ?>">IMEI管理</a>
            <a href="index.php?mod=promotion&action=export&id=<?php echo $item['id']; ?>">IMEI导出</a>
            <?php if(!$item['is_current']){ ?>
            <a href="#" data-id="<?php echo $item['id']; ?>" class="set">设置为当前活动</a>
            <?php } ?>
            
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
$('.set').click(function(){
  if(!confirm('确定要设置为当前活动吗？')){
    return;
  }

  var id    = $(this).attr('data-id');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=promotion&action=set_current&wants_json=true",
    data: {id: id},
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      if(res && res.code==200){
          submit_alert('操作成功！');
          window.location.reload();
      }else{
        submit_alert(res.message);
      }
    }
  });
});
</script>


    </body>
</html>