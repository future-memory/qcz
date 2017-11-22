<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  .panel {
    border: none;
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
</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=master">用户管理</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=user_edit">添加用户</a></li>
      <li role="presentation"><a href="index.php?mod=master&action=role_list">角色管理</a></li>
      <li role="presentation" class="active"><a href="#">角色权限</a></li>
    </ul>
  </div>
  <p></p>
  <div>
    <form id="form1" action="index.php?mod=master&action=role_update" data-return-url="index.php?mod=master&action=role_list">
      <input type="hidden" value="<?php echo $id; ?>" name="id">
      
      <div class="row">
        <h2 style="font-size: 16px;margin:10px 0;">角色：<?php echo $role['name']; ?></h2>
      </div>

      <div class="row">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">基础</h3>
          </div>
            <div class="panel-body">
          <label class="checkbox-inline">
            <input type="checkbox" name="mods[]" value="_allowpost" <?php echo in_array('_allowpost', $permed) ? 'checked="checked"' : ''; ?> > 可读写，不选此项 将只能查看不可更改记录
          </label>
            </div>
        </div>
      </div>


      <?php
      foreach ($menu_list['menu'] as $key => $submenu) {
          if(empty($submenu['submenu'])){
            continue;
          }
      ?>
      <div class="row">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><?php echo $submenu['name']; ?></h3>
          </div>
            <div class="panel-body">
        <?php foreach ($submenu['submenu'] as $k => $menu) { ?>
          <label class="checkbox-inline">
            <input type="checkbox" name="mods[]" value="<?php echo $menu['mod']; ?>" <?php echo in_array($menu['mod'], $permed) ? 'checked="checked"' : ''; ?> <?php echo isset($menu['ctrl']) && $menu['ctrl'] && !$is_founder ? 'onclick="return false;"' : ''; ?> ><?php echo $menu['name']; ?>
          </label>
        <?php } ?>       
            </div>
        </div>
      </div>
      <?php
      }
      ?>
      <div class="row">
        <button type="button" class="btn btn-primary" name="editsubmit" id="submit-btn">提交</button>
      </div>
    </form>
  </div>
</div>

<?php include(APP_ROOT . '/template/common/footer.php'); ?>

    </body>
</html>