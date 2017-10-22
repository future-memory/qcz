<?php include(APP_ROOT . '/template/common/header.php'); ?>
<script type="text/javascript" src="assets/datepicker/WdatePicker.js"></script>
<style type="text/css">
  form {
    margin-top: 20px;
  }
  .row {
    margin-left: 0px;
    margin-right: 0px;
    margin-top: 20px;
  }
  .hide {
    display: none;
  }
</style>
<div class="container-fluid">

  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="/index.php?mod=enroll">活动列表</a></li>
      <li role="presentation"><a href="/index.php?mod=enroll&action=add">新增或编辑</a></li>
      <li><a href="/index.php?action=app&mod=enroll&action=apply_list&enroll_id=<?php echo $enroll_id; ?>"><span>报名列表</span></a></li>
      <li class="active"><a href="#"><span>报名</span></a></li>
    </ul>
  </div>

  <div class="row">
    当前发布会：
    <span><?php echo $enroll['name']; ?></span>
  </div>
  
  <div class="row" style="padding: 0 30px; margin-top: 0">
    <form id="form1" data-return-url="/index.php?mod=enroll&action=apply_list" data-force-return="1" class="form-horizontal" method="post" action="index.php?mod=enroll&action=apply_update" name="form" enctype="multipart/form-data">
      <input type="hidden" value="<?php echo $id; ?>" name="id">
      <input type="hidden" value="<?php echo $enroll_id; ?>" name="enroll_id">

      <div class="form-group">
        <label for="uid">uid</label>
        <input type="text" name="uid" value="<?php echo isset($apply['uid']) ? $apply['uid'] : ''; ?>" class="txt">
      </div>

      <div class="form-group">
        <label for="realname">姓名</label>
        <input type="text" name="realname" value="<?php echo isset($apply['realname']) ? $apply['realname'] : ''; ?>" class="txt">
      </div>

      <div class="form-group">
        <label for="name">手机</label>
        <input type="text" name="mobile" value="<?php echo isset($apply['mobile']) ? $apply['mobile'] : ''; ?>" class="txt">
      </div>

<?php foreach($extcolumn as $column){ ?>

      <div class="form-group">
        <label for="<?php echo $column['key']; ?>"><?php echo $column['name']; ?></label>

<?php if(!empty($column['range'])){ ?>
        <select name="<?php echo $column['key']; ?>" id="<?php echo $column['key']; ?>" style="min-width: 50px">
        <?php foreach ($column['range'] as $r) { ?>
            <option value="<?php echo $r['val']; ?>" <?php echo isset($extdata[$column['key']]) && $extdata[$column['key']] == $r['val'] ? 'selected' : ''; ?> >
            <?php echo $r['name']; ?>
            </option>
        <?php } ?>
        </select>
<?php }else{ ?>
        <input type="text" name="<?php echo $column['key']; ?>" id="<?php echo $column['key']; ?>" value="<?php echo isset($extdata[$column['key']]) ? $extdata[$column['key']] : ''; ?>" class="txt">
<?php } ?>

      </div>
<?php } ?>

      <div class="form-group">
        <label for="invitation_pic">邀请函图</label>

      <?php if (isset($apply['invitation_pic']) && $apply['invitation_pic']): ?>
        <img src="<?php echo $this->_get_pic_url($apply['invitation_pic'])?>" style="width: 100px; height: 100px">
      <?php endif ?>       

        <input type="file" style="width:300px;" name="invitation_pic" id="invitation_pic" class="form-control" value="">
        或：<br>
        <input type="text" style="width:300px;" name="invitation_picu" value="<?php echo isset($apply['invitation_pic']) && $apply['invitation_pic'] ? $this->_get_pic_url($apply['invitation_pic']) : ''; ?>">

      </div>

      <div class="form-group">
        <label for="apply_reason">申请理由</label>
        <textarea class="form-control" name="apply_reason" rows="6" id="textarea"><?php echo isset($apply['apply_reason']) ? $apply['apply_reason'] : ''; ?></textarea> 
      </div>      

      <div class="form-group">
        <label for="cancel_reason">取消理由</label>
        <textarea class="form-control" name="cancel_reason" rows="6" id="textarea"><?php echo isset($apply['cancel_reason']) ? $apply['cancel_reason'] : ''; ?></textarea> 
      </div>    

      <div class="form-group">
        <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>
      </div>
    </form>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
  var is_uploading = false;
  var serialize_url = '';

  $('#_submit-btn').click(function(){

    if (get_uploading_status()) {
      submit_alert('正在上传图片，请稍后...');
      return false;
    }
    upload_pic($('#pic'), 'pic', function() {
      upload_pic($("#invitation_pic"), "invitation_pic", function() {
        post_data();
      });
    });
    // upload_pic($('#invitation_pic'), 'invitation_pic', post_data());
  });

  function set_uploading_status(status) {
    is_uploading = status;
  }

  function get_uploading_status() {
    return is_uploading;
  }

  function upload_pic(file_obj, param, callback) {

    set_uploading_status(true);

    var file_data;
    if (file_obj.prop('files')) {
      var file_data = file_obj.prop('files')[0];     
    }
    if (file_data) {

      if (file_data.size > 1000 * 1024) {

        set_uploading_status(false);
        
        submit_alert('图片尺寸过大');
        return false;
      }

      var form_data = new FormData();
      form_data.append('file', file_data);

      $.ajax({
        type: "POST",
        url: 'index.php?wants_json=true',
        data: 'mod=attachment&action=upyun_sign&module=app',
        dataType: 'json',
        error: function(request) {

          set_uploading_status(false);

          submit_alert("错误，请重试");
        },
        success: function(res) {
          if(res && res.code==200){

            form_data.append('signature', res.data.signs[0].signature);
            form_data.append('policy', res.data.signs[0].policy);

            $.ajax({
              url: res.data.url,
              dataType: 'text',
              cache: false,
              contentType: false,
              processData: false,
              data: form_data,                         
              type: 'post',
              error: function(request) {
                
                set_uploading_status(false);

                request = JSON.parse(request.responseText);
                submit_alert('上传图片错误: ' + request.message);
              },
              success: function(_res){
                _res = _res.replace(/image-/g, 'image_');
                _res = JSON.parse(_res);
                if (_res && _res.code == 200) {
                  serialize_url += '&' + param + '=' + _res.url;
                  if (callback) {
                    callback();
                  } else {
                    set_uploading_status(false);
                  }
                }
              }
             });
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
    } else {
      set_uploading_status(false);
      if (callback) {
        callback();
      }
    }
  }

  function post_data() {
    var data = $('#form1').serialize() + serialize_url;
    $.ajax({
      type: "POST",
      url: "index.php?mod=enroll&action=apply_update&wants_json=true",
      data: data,
      dataType: 'json',
      error: function(request) {

        set_uploading_status(false);

        submit_alert("错误，请重试");
      },
      success: function(res) {

        set_uploading_status(false);
        
        if(res && res.code==200){
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