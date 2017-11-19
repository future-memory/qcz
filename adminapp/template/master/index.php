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
      <li role="presentation" class="active"><a href="#">用户管理</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=user_edit">添加用户</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=role_list">角色管理</a></li>
    </ul>
  </div>
  <div class="row">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
            <th>成员用户</th>
            <th>角色</th>
            <th>domain</th>
            <th>操作</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($members as $key => $member) { ?>
        <tr>
        <td><?php echo $member['username']; ?></td>
        <td><?php echo in_array($member['username'], $founders) ? 'Master' : (isset($roles[$member['role_id']]) ? $roles[$member['role_id']]['name'] : '-'); ?></td>
        <td><?php echo $member['domain']; ?></td>
        <td>
          <?php if(in_array($member['username'], $founders)){ ?>
          -
          <?php }else{ ?>
          <a href="index.php?mod=master&action=user_edit&uid=<?php echo $member['uid']; ?>">编辑</a>
          <a href="#" data-uid="<?php echo $member['uid']; ?>">删除</a>
          <?php } ?>
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
  var uid     = $(this).attr('data-uid');

  $.ajax({
    type: "POST",
    url:  "index.php?mod=master&action=user_delete&wants_json=true",
    data: {uid: uid},
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