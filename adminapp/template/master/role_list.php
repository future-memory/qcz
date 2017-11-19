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
      <li role="presentation"><a href="index.php?mod=master">用户管理</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=user_edit">添加用户</a></li>
      <li role="presentation" class="active"><a href="#">角色管理</a></li>
    </ul>
  </div>
  <div class="row">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
            <th>角色</th>
            <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($roles as $role) { ?>
          <tr>
            <td><?php echo $role['name']; ?></td>
            <td>
              <?php if(!$is_founder && $role['name']=='超级管理员'){ ?>
              <?php }else{?>
              <a href="index.php?mod=master&action=role_edit&id=<?php echo $role['id']; ?>">权限管理</a>
              <?php } ?>

              <?php if(!$is_founder && (!$role['domain'] || $role['domain']=='www')){ ?>
              <?php }else{?>
              <a href="#" data-id="<?php echo $role['id']; ?>" class="del">删除</a>
              <?php } ?>
            </td>
          </tr>    
       <?php }?>                              
      </tbody>
    </table>    
  </div>
  <div class="row" style="margin-bottom: 100px">
    <form action="index.php?mod=master&action=role_add" method="post" name="form" enctype="multipart/form-data" id="form1" data-return-url="index.php?mod=master&action=role_list">
      <div>
        <input type="text" name="name" id="" required placeholder="新增角色" value="">        
        <button type="button" class="btn btn-primary" name="editsubmit" id="submit-btn">提交</button>
      </div>
    </form>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

$('.del').click(function(){
  if(!confirm('确定要删除吗？删除不可恢复')){
    return;
  }
  var _parent = $(this).parent().parent();
  var id      = $(this).attr('data-id');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=master&action=role_delete&wants_json=true",
    data: {id: id},
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