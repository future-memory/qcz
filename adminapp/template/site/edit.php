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
      <li role="presentation"><a href="index.php?mod=site">站点列表</a></li>
      <li role="presentation" class="active">
        <a href="#"><?php echo $site ? '编辑' : '添加'; ?>站点</a>
      </li>
    </ul>
  </div>
  <p></p>
  <div>
    <form action="index.php?mod=site&action=update" class="form-horizontal" method="post" name="form" enctype="multipart/form-data" data-return-url="index.php?mod=site" id="form1">
      <input type="hidden" value="<?php echo isset($site['domain']) ? $site['domain'] : ''; ?>" name="old_domain" >

      <div class="form-group">
        <label for="" class="col-sm-2 control-label">domain</label>
        <div class="col-sm-6"><input class="form-control width200" type="text" name="domain"  value="<?php echo isset($site['domain']) ? $site['domain'] : ''; ?>"></div>
      </div>
      
      <div class="form-group">
        <label for="" class="col-sm-2 control-label">名称</label>
        <div class="col-sm-6"><input class="form-control width200" type="text" name="name"  value="<?php echo isset($site['name']) ? $site['name'] : ''; ?>"></div>
      </div>

     <div class="form-group">
        <label for="" class="col-sm-2 control-label">appkey</label>
        <div class="col-sm-6"><input class="form-control width200" type="text" name="appkey"  value="<?php echo isset($site['appkey']) ? $site['appkey'] : ''; ?>"></div>
      </div>
      
      <div class="form-group">
        <label for="" class="col-sm-2 control-label">secret</label>
        <div class="col-sm-6"><input class="form-control width200" type="text" name="secret"  value="<?php echo isset($site['secret']) ? $site['secret'] : ''; ?>"></div>
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