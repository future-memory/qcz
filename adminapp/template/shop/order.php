<?php include(APP_ROOT . '/template/common/header.php'); ?>
<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>
<link rel="stylesheet" href="assets/kd/themes/default/default.css" />
<script charset="utf-8" src="assets/kd/kindeditor-all-min.js"></script>
<script charset="utf-8" src="assets/kd/lang/zh-CN.js"></script>
<style type="text/css">
  .row {
    margin-left: 0;
    margin-right: 0;
  }
  table.fixed { table-layout:fixed; }
  table.fixed td { overflow: hidden; }

  #search-form span {
    margin-left: 20px;
  }

  #search-btn {
    margin-left: 20px;
  }
</style>
<div class="container-fluid">
  <p></p>
  <div>
    <ul class="nav nav-tabs">
      <?php foreach ($this->tabs as $action => $action_name): ?>
        <?php if ($this->current_action == $action): ?>
      <li role="presentation" class="active"><a href="#"><?=$action_name?></a></li>
        <?php else: ?>
      <li role="presentation"><a href="index.php?mod=shop&action=<?=$action?>"><?=$action_name?></a></li>
        <?php endif ?>
      <?php endforeach ?>
    </ul>
  </div>
  <p>
  <div class="inline">
    <form method="get" action="index.php" id="search-form">
      <span>状态 </span>
      <select name="status" class="form-control" style="width: 120px;display: inline">
        <option value="">全部</option>
        <?php foreach ($order_status_array as $k => $v): ?>
        <option value="<?=$k; ?>" <?=$status===$k ? 'selected' : ''; ?>><?=$v; ?></option>
        <?php endforeach ?>
      </select>

      <span>商品 </span>
      <select name="goods_id" class="form-control" style="width: 150px;display: inline">
        <option value="">全部</option>
        <?php foreach ($goods_list_all as $goods): ?>
        <option value="<?=$goods['id']; ?>" <?=$goods_id===intval($goods['id']) ? 'selected' : ''; ?>><?=$goods['name']; ?></option>        
        <?php endforeach ?>
      </select>

      <span>日期时间</span>
      <input type="text" placeholder="开始时间" name="start_time" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="<?=is_numeric($start_time) ? date('Y-m-d H:i:s', $start_time) : $start_time; ?>" style="width: 180px;display: inline">-
      <input type="text" placeholder="结束时间" name="end_time" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="<?=is_numeric($end_time) ? date('Y-m-d H:i:s', $end_time) : $end_time; ?>" style="width: 180px;display: inline"> 
      <input type="hidden" name="mod" value="shop">
      <input type="submit" class="btn btn-primary" id="search-btn" value="搜索">

      <a id="export" href="javascript:;" onclick="return false;" style="display: inline;float: right;">导出csv</a>

    </form>
  </div>
  <p>
  <form name="form" method="post" class="form-horizontal" action="" id="audit-form">
    <div>
      <table class="table table-striped fixed">
        <thead>
          <col width="20px"></col>
          <col width="30px"></col>
          <col width="50px"></col>
          <col width="50px"></col>
          <col width="60px"></col>
          <col width="100px"></col>
          <col width="80px"></col>
          <col width="30px"></col>
          <col width="100px"></col>
          <col width="50px"></col>
          <col width="50px"></col>
          <tr>
            <th>
              <span class="checkbox">
                <label>
                  <input type="checkbox" name="all" id="select-all" value="1">
                </label>
              </span>
            </th>
            <th>id</th>
            <th>UID</th>
            <th>收货人</th>
            <th>电话</th>
            <th>收货地址</th>
            <th>购买商品</th>
            <th>总计</th>
            <th>购买时间</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $item): ?>
          <tr>
            <td><input type="checkbox" class="id" name="ids[]" value="<?php echo $item['id']; ?>"></td>
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['uid']; ?></td>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['phone']; ?></td>
            <td><?php echo $item['address'];  ?></td>
            <td>
            <?php 
              $sum = 0;
              if(isset($goods_list[$item['id']])){
                $addon = '';
                foreach ($goods_list[$item['id']] as $value) {
                  echo $addon . $value['name'] . ' x ' . $value['count'];
                  $addon = '</br>';
                  $sum += $value['price'] * $value['count'];
                }
              }else{
                echo  '-'; 
              }
              //echo $item['goods_name'] . ' * ' . $item['count'];
            ?>
            </td>
            <td><?php echo $sum; ?></td>
            <td><?php echo $item['dateline'] ? date('Y-m-d H:i:s', $item['dateline']) : '-'; ?></td>  
            <td>
            <?php 
              echo $order_status_array[$item['status']];
              echo ($item['status']>2 && $item['status']<8 ? '（快递：'.$item['deliver'].'，单号：'.$item['delivery_sn'].'）' : '');
             ?>
             </td>
            <td>
              <?php if($item['status']==9){ ?>
              <a href="index.php?mod=shop&action=order_audit&id=<?php echo $item['id']; ?>&url=<?php echo $url; ?>">审核</a>&nbsp;&nbsp;
              <?php } elseif($item['status']==1){ ?>
              <a href="index.php?mod=shop&action=order_delivery&id=<?php echo $item['id']; ?>&url=<?php echo $url; ?>">发货</a>&nbsp;&nbsp;
              <?php } elseif($item['status']==3){ ?>
              <a onclick="order_done(<?=$item['id']?>)" href="javascript:;">置为完成</a>&nbsp;&nbsp;
              <?php }else{echo '-';} ?>
            </td>
          </tr>       
        <?php endforeach ?>
        </tbody>
      </table>
      <?=$pager?>
      <div id="audit-tbl" style="display: none">
        <div>操作</div>

        <div class="form-group">
          <label class="col-sm-1 control-label">状态</label>
          <div class="col-sm-7">
            <label class="radio-inline">
              <input type="radio" name="status" value="1"> 通过
            </label>
            <label class="radio-inline">
              <input type="radio" name="status" value="2"> 不通过
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-1 control-label">是否发送系统消息</label>
          <div class="col-sm-7">
            <label class="radio-inline">
              <input type="radio" name="send" value="1"> 是
            </label>
            <label class="radio-inline">
              <input type="radio" name="send" value="0"> 否
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-1 control-label">系统消息内容</label>
          <div class="col-sm-7">
            <textarea class="form-control" cols="50" rows="6" name="message" id="message" rows="6" style="width:600px">您的订单（<a href="">点击查看</a>）{audit_result}审核。</textarea>
            <p class="bg-success">
              <span class="help-block">{audit_result}会被替换为已通过或未通过</span>
            </p>
          </div>
        </div>

        <div class="form-group">
          <span class="col-sm-1"></span>
          <div class="col-sm-7">
            <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>  
          </div>
        </div>

      </div>

    </div>
  </form>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

$('#export').click(function(){
  var _url = window.location.href;
  window.location.href = _url + '&action=export';
});

var editor;
KindEditor.ready(function(K) {
  editor = K.create('textarea[name="message"]', {
    resizeType : 1,
    allowPreviewEmoticons : false,
    allowImageUpload : true,
    afterBlur:function(){
        this.sync();
    },        
    items : [
      'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
      'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
      'insertunorderedlist', '|', 'link']
  });
});

$('#select-all').change(function(){
    if($(this).is(':checked')){
        $('.id').prop('checked',true);
        $('#audit-tbl').show();
    }else {
        $('.id').prop("checked", false);
        $('#audit-tbl').hide();
    }
});

$('.id').change(function(){
    $('#select-all').attr('checked',false);
    if($('.id:checked').length>0){
      $('#audit-tbl').show();
  }else{
    $('#audit-tbl').hide();
  }
});

function order_done(id) {
  post_data('index.php?mod=shop&action=order_done&id=' + id, '', window.location.href);
}

var request;
var loading = false;
$("#_submit-btn").click(function() {
  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }
  loading = true;
  var formElement = document.querySelector("#audit-form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=shop&action=batch_audit");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      console.log(request.responseText);
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {

        submit_confirm(res.message, window.location.href, 1);
      } else {
        submit_alert(res.message);
      }
    } else {
      alert("Problem retrieving XML data");
    }
    loading = false;
  }
}

function post_data(url, data, _returl = '') {
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
        if (_returl) {
          submit_confirm(res.message, _returl, 1);
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