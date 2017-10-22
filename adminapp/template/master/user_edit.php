<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  .panel {
    border: none;
  }
  .row {
  	margin-left: 0;
  	margin-right: 0;
  }
</style>
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
    .form-horizontal .control-label{
      padding-top: 0;
    }
    .width200{width: 200px;}
</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=master">用户管理</a></li>
      <li role="presentation" class="active"><a href="#">用户编辑</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=role_list">角色管理</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form action="index.php?mod=master&action=user_update" class="form-horizontal" method="post" name="form" enctype="multipart/form-data" data-return-url="index.php?mod=master" id="form1">
      <input type="hidden" value="<?php echo $uid; ?>" name="uid" id="uid">
      
      <div class="form-group">
        <label for="" class="col-sm-2 control-label">用户名</label>
        <div class="col-sm-6"><input class="form-control width200" type="text" name="username"  value="<?php echo isset($user['username']) ? $user['username'] : ''; ?>"></div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">密码</label>
        <div class="col-sm-6">
          <input class="form-control width200" type="text" name="password"  value="">
          <?php if(isset($user['uid']) && $user['uid']){ ?>
          <p class="bg-success width200">
            <span class="help-block">不填写将保留原密码</span>
          </p>
          <?php } ?>
        </div>
      </div>    

      <div class="form-group">
      	<label class="col-sm-2 control-label">所属用户组</label>
        <div class="col-sm-6">
      	<select name="role_id" id="role_id">
      	<?php
      		foreach ($roles as $role) {
      			if ((int)$role['id'] === (int)$role_id) {
      				echo '<option value="'. $role['id'] .'" selected>' . $role['name'] . '</option>';
      			} else {
      				echo '<option value="'. $role['id'] .'">' . $role['name'] . '</option>';
      			}
      		}
      	?>
      	</select>
        </div>
      </div>
      
      <div class="form-group">
        <span class="col-sm-2"></span>
        <div class="col-sm-6">
          <button type="button" class="btn btn-primary" name="editsubmit" id="submit-btn">提交</button>  
        </div>
      </div>

    </form>
  </div>
</div>

<?php include(APP_ROOT . '/template/common/footer.php'); ?>

    </body>
</html>