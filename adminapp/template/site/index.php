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
    <ul class="nav nav-tabs">
      <li role="presentation" class="active"><a href="#">站点列表</a></li>
      <li role="presentation"><a href="index.php?mod=site&action=edit">添加站点</a></li>
    </ul>
  </div>
  <div class="row">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
            <th>domain</th>
            <th>站点名称</th>
            <th>appkey</th>
            <th>操作</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($sites as $key => $site) { ?>
        <tr>
        <td><?php echo $site['domain']; ?></td>
        <td><?php echo $site['name']; ?></td>
        <td><?php echo $site['appkey']; ?></td>
        <td> 
          <a href="index.php?mod=site&action=edit&domain=<?php echo $site['domain']; ?>">编辑</a>
          <a href="index.php?mod=site&action=perm&domain=<?php echo $site['domain']; ?>">权限管理</a>
          <!--<a href="#" data-domain="<?php echo $site['domain']; ?>">删除</a>-->
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
  if(!confirm('确定要删除吗？删除不可恢复')){
    return;
  }
  var _parent = $(this).parent().parent();
  var domain     = $(this).attr('data-domain');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=site&action=delete&wants_json=true",
    data: {domain: domain},
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