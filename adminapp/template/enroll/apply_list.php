<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  .table>tbody>tr>td {
        border-top: none;
  }
  .table>thead>tr>th {
        border-bottom: none;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }
  .row {
    margin-left: 0px;
    margin-right: 0px;
    margin-top: 10px;
  }
</style>
<div class="container-fluid">
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=enroll">活动列表</a></li>
      <li role="presentation"><a href="index.php?mod=enroll&action=add">新增或编辑</a></li>
      <li  class="active"><a href="#"><span>报名列表</span></a></li>
      <li><a  href="index.php?action=app&mod=enroll&action=apply_add&enroll_id=<?php echo $enroll['id']; ?>"><span>报名</span></a></li>
    </ul>
  </div>
  <div class="row">
      当前发布会：
      <span><?php echo $enroll['name']; ?></span>

      <span style="position:absolute;right:50px;">
        <select name="status" id="status" style="min-width: 150px">
          <?php echo $status_options; ?>
        </select>
        <a href="index.php?action=app&mod=enroll&action=export&enroll_id=<?php echo $enroll['id']; ?>&status=<?php echo $status; ?>">导出</a>

        <input type="file" style="margin-left:20px;width:170px;display:inline-block;" id="importfile" value="">
        <a href="javascript:;" id="import" onclick="return false;">导入</a>
      </span>
  </div>
  <div class="row">
    <table class="table table-striped fixed">
      <thead>
        <col width="80px"></col>
        <col width="80px"></col>
        <col width="100px"></col>
        <col width="80px"></col>
        <col></col>
        <col width="160px"></col>
        <tr>          
          <th>uid</th>
          <th>姓名</th>
          <th>手机</th>
          <th>状态</th>
          <th>申请理由</th>
          <th>操作</th>
        </tr>
    	</thead>
    	<tbody>
    		<?php foreach ($alist as $key => $value): ?>
        <tr>
          <td><?php echo $value['uid']; ?></td>
          <td><?php echo $value['realname']; ?></td>
          <td><?php echo $value['mobile']; ?></td>
          <td>
          <?php echo isset($status_array[$value['status']]) ? $status_array[$value['status']] : ''; ?>
          </td>
          <td><?php echo $value['apply_reason']; ?></td>
          <td>
          <?php if($enroll['need_verify'] && !in_array($value['status'], array(1,2,3,4))){?>
            <a onclick="change_status(<?php echo $value['id']; ?>, 1);return false;" href="javascript:;" hidefocus="true">通过</a>
          <?php } ?>
          <?php if($enroll['need_verify'] && !in_array($value['status'], array(-1,2,3,4))){?>  
            <a onclick="change_status(<?php echo $value['id']; ?>, -1);return false;" href="javascript:;" hidefocus="true">不通过</a>
          <?php } ?>
            <a href="/index.php?mod=enroll&action=apply_add&id=<?php echo $value['id']; ?>&enroll_id=<?php echo $enroll['id']; ?>">编辑</a>
            <a onclick="delete_apply(<?php echo $value['id']; ?>);return false;" href="javascript:;" hidefocus="true">删除</a>
          </td>
        </tr>
    		<?php endforeach ?>
    	</tbody>
    </table>
  </div>
  <?php echo $pager; ?>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
  var url = 'index.php?mod=enroll&action=apply_list&enroll_id=<?php echo $enroll['id']; ?>';
  $('#status').change(function() {
    window.location.href = url + '&status=' + $(this).val();
  });

  $('#import').click(function() {
     doimport($('#importfile'));
  });

function change_status(id, status) {
  var sure = window.confirm('确定'+(status==1 ? '通过' : '不通过')+'？');
  if(sure) {
    var url = "index.php?mod=enroll&action=verify&wants_json=true";
    var data = 'id=' + id + '&status=' + status;
    post_data(url, data);
  }
}

function delete_apply(id) {
  var sure = window.confirm('删除将不可恢复！确定删除？');
  if(sure) {
    var url = "index.php?mod=enroll&action=delete_apply&wants_json=true";
    var data = 'id=' + id;
    post_data(url, data);
  }
}

function post_data(url, data) {
  $.ajax({
    type: "POST",
    url: url,
    data: data,
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
      res.returl = window.location.href;
      if(res && res.code == 200) {
        if (res.returl) {
          submit_confirm(res.message, res.returl, 1);
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

function doimport(file_obj) {
  var file_data;
  if (file_obj.prop('files')) {
    var file_data = file_obj.prop('files')[0];     
  }
  if(!file_data){
    alert('请选择需要导入的文件');
    return false;
  }
  if(file_data) {
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('enroll_id', '<?php echo $enroll['id']; ?>');

    $.ajax({
      url: '/index.php?mod=enroll&action=import',
      dataType: 'text',
      cache: false,
      contentType: false,
      processData: false,
      data: form_data,                         
      type: 'post',
      error: function(request) {
        request = JSON.parse(request.responseText);
        submit_alert('错误: ' + request.message);
      },
      success: function(_res){
        _res = JSON.parse(_res);
        if (_res && _res.message) {
          submit_confirm(_res.message, window.location.href, (_res.code==200 || _res.code==201 ? 1 : 0));
        }else{
          alert('error');
        }
      }
     });
  }
}

</script>
    </body>
</html>