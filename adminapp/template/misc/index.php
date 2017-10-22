<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  .table>tbody>tr>td {
      border-top: none;
  }
  .table>thead>tr>th {
      border-bottom: none;
  }
  form {
    margin-top: 20px;
  }
  .row {
    margin-left: 0px;
    margin-right: 0px;
  }
</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation" class="active"><a href="#">主题列表</a></li>
      <li role="presentation"><a href="index.php?mod=misc&action=edit_subject">添加主题</a></li>
    </ul>
  </div>
  <div class="row">
    <table class="table table-striped">
      <thead>
        <tr>
        	<th>key</th>
        	<th>name</th>
        	<th>管理</th>
        </tr>
    	</thead>
    	<tbody>
    		<?php foreach ($list as $key => $value): ?>
    			<tr>
    				<td><?=$value['key']?></td>
    				<td><?=$value['name']?></td>
    				<td>
    					<?php if ($is_founder): ?>
    						<a href="index.php?mod=misc&action=edit_subject&key=<?=$value['key']?>">修改</a>
    					<?php endif ?>
    					  <a href="index.php?mod=misc&action=data&key=<?=$value['key']?>">内容</a>
    				</td>
    			</tr>
    		<?php endforeach ?>
    	</tbody>
    </table>
  </div>
<?=$pager?>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
    </body>
</html>