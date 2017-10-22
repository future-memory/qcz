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
  form {
    margin-top: 20px;
  }
  .row {
    margin-left: 0px;
    margin-right: 0px;
    margin-top: 10px;
  }
</style>
<div class="container-fluid">
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation" class="active"><a href="#">活动列表</a></li>
      <li role="presentation"><a href="index.php?mod=enroll&action=add">新增或编辑</a></li>
    </ul>
  </div>
  <div class="row">
    当前发布会
    <span id="current_enroll" name="<?=$current_enroll_id?>"><?=$current_enroll_name?></span>
    <input name="current_enroll_btn" id="current_enroll_btn" type="button" onclick="set_current_enroll();" value="编辑">
    <input name="new_enroll_btn" id="new_enroll_btn" type="button" onclick="set_new_enroll();" value="确定" style="display: none">
  </div>
  <div class="row">
    <table class="table table-striped fixed">
      <thead>
        <col width="30px"></col>
        <col width="150px"></col>
        <col width="120px"></col>
        <col width="120px"></col>
        <col width="50px"></col>
        <col width="120px"></col>
        <col width="150px"></col>
        <tr>
          <th>id</th>
          <th>活动名称</th>
          <th>开始时间</th>
          <th>结束时间</th>
          <th>人数</th>
          <th>邀请时间</th>
          <th>操作</th>
        </tr>
    	</thead>
    	<tbody>
    		<?php foreach ($clist as $key => $value): ?>
        <tr>
          <td><?=$value['id']?></td>
          <td><?=$value['name']?></td>
          <td><?=$value['begin']?></td>
          <td><?=$value['end']?></td>
          <td><?=$value['num']?></td>
          <td><?=$value['invitation_time']?></td>
          <td>
            <a href="/index.php?mod=enroll&action=add&id=<?=$value['id']?>">编辑</a>
            <a href="/index.php?mod=enroll&action=apply_list&enroll_id=<?=$value['id']?>">报名列表</a>
            <a onclick="delete_enroll(<?=$value['id']?>);return false;" href="javascript:;" hidefocus="true">删除</a>
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

function set_current_enroll() {
  $("#current_enroll_btn").hide();
  $("#new_enroll_btn").show();

  var current_enroll = $("#current_enroll").text();
  var current_enroll_html = $("#current_enroll").html();
  var current_enroll_id =  $("#current_enroll").attr("name");
  if(current_enroll == current_enroll_html){
    var replace_html = '<input type="text" style="width:200px;" id="new_enroll" name="current_enroll_id" value="'+current_enroll_id+'" >';
    $("#current_enroll").html(replace_html);
  }

}

function delete_enroll(id) {
  var is_delete = window.confirm('是否删除？');
  if (is_delete) {
    var url = "index.php?mod=enroll&action=delete_enroll&wants_json=true";
    var data = 'id=' + id;
    post_data(url, data);
  }
}

function set_new_enroll() {
  var url = "index.php?mod=enroll&action=update_enroll_id&wants_json=true";
  var data = 'id=' + $('#new_enroll').val();
  post_data(url, data);
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
</script>
    </body>
</html>