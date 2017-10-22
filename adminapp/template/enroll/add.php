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
      <li role="presentation"><a href="index.php?mod=enroll">活动列表</a></li>
      <li role="presentation" class="active"><a href="#">新增或编辑</a></li>
    </ul>
  </div>
  <div class="row" style="padding: 0 30px; margin-top: 0">
    <form id="form1" data-return-url="/index.php?mod=enroll" data-force-return="1" class="form-horizontal" method="post" action="index.php?mod=enroll&action=add" name="form" enctype="multipart/form-data">
      <input type="hidden" value="<?=isset($enroll['id']) ? $enroll['id'] : ''?>" name="enroll_id">

      <div class="form-group">
        <label for="name">标题</label>
        <input type="text" name="name" style="width:185px;" value="<?=isset($enroll['name']) ? $enroll['name'] : ''?>" class="form-control">
      </div>

      <div class="form-group">
        <label for="begin">报名开始</label>
        <input type="text" name="begin" style="width:185px;" id="begin" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss', maxDate:'#F{$dp.$D(\'end\')}'})" value="<?=isset($enroll['begin']) && $enroll['begin'] ? $enroll['begin'] : ''?>">
      </div>

      <div class="form-group">
        <label for="end">报名结束</label>
        <input type="text" name="end" style="width:185px;" id="end" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss', minDate:'#F{$dp.$D(\'begin\')}'})" value="<?=isset($enroll['end']) && $enroll['end'] ? $enroll['end'] : ''?>">
      </div>


      <div class="form-group">
        <label for="start">活动开始</label>
        <input type="text" name="start" style="width:185px;" id="start" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="<?=(isset($enroll['start']) && $enroll['start']) ? $enroll['start'] : ''?>"><!--, minDate:'#F{$dp.$D(\'end\')}'}-->
      </div>


      <div class="form-group">
        <label for="num">招募人数</label>
        <input type="text" name="num" style="width:185px;" id="num" value="<?=isset($enroll['num']) ? $enroll['num'] : ''?>" class="form-control">
      </div>

      <div class="form-group">
        <label for="name">是否需审核</label>
        <select name="need_verify" id="need_verify" class="form-control" style="width:185px">
            <option value="0" <?php echo isset($enroll['need_verify']) && $enroll['need_verify']==0 ? 'selected' : ''; ?> >
            无需审核
            </option>
            <option value="1" <?php echo isset($enroll['need_verify']) && $enroll['need_verify']==1 ? 'selected' : ''; ?> >
            需运营审核，无需用户确认
            </option>
            <option value="2" <?php echo isset($enroll['need_verify']) && $enroll['need_verify']==2 ? 'selected' : ''; ?> >
            需运营审核，且需用户确认
            </option>                      
        </select>
      </div>

      <div class="form-group">
        <label for="reward_mq">报名未通过补偿</label>
        <input type="text" name="reward_mq" style="width:185px;" value="<?=isset($enroll['reward_mq']) ? $enroll['reward_mq'] : ''?>" class="form-control">
      </div>

      <div class="form-group">
        <label for="invitation_time">邀请时间</label>
        <input type="text" name="invitation_time" style="width:185px;" id="invitation_time" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="<?=isset($enroll['invitation_time']) && $enroll['invitation_time'] ? $enroll['invitation_time'] : ''?>">
      </div>

      <div class="form-group">
        <label for="address">活动地点</label>
        <input type="text" name="address" style="width:300px;" id="address" value="<?=isset($enroll['address']) ? $enroll['address'] : ''?>" class="form-control">
      </div>      

      <div class="form-group">
        <label for="pic">海报</label>
        <input type="file" style="width:300px;" name="pic" id="pic" class="form-control" value="">
      </div>

      <?php if (isset($enroll['pic']) && $enroll['pic']): ?>
      <div class="form-group">
        <img src="<?=$this->_get_pic_url($enroll['pic'])?>" style="width: 100px; height: 100px">
      </div>
      <?php endif ?>


      <div class="form-group">
        <label for="invitation_pic">邀请函图片</label>
        <input type="file" name="invitation_pic" style="width:300px;" id="invitation_pic" class="form-control" value="">
      </div>

      <?php if (isset($enroll['invitation_pic']) && $enroll['invitation_pic']): ?>
      <div class="form-group">
        <img src="<?=$this->_get_pic_url($enroll['invitation_pic'])?>" style="width: 100px; height: 100px">
      </div>
      <?php endif ?>

      <div class="form-group">
        <label for="description">特别说明（首页）</label>
        <textarea class="form-control" name="description" rows="6" id="textarea"><?=isset($enroll['description']) ? $enroll['description'] : ''; ?></textarea> 
      </div>

      <div class="form-group">
        <label for="sucess_tips">报名成功后文案</label>
        <textarea class="form-control" name="sucess_tips" rows="6" id="textarea"><?=isset($enroll['sucess_tips']) ? $enroll['sucess_tips'] : ''; ?></textarea> 
      </div>
      
<!--
      <div class="form-group">
        <label for="notes">注意事项</label>
        <textarea class="form-control" name="notes" rows="6" id="textarea"><?=isset($enroll['notes']) ? $enroll['notes'] : ''; ?></textarea> 
      </div>

      <div class="form-group">
        <label for="busline">乘车</label>
        <textarea class="form-control" name="busline" rows="6" id="textarea"><?=isset($enroll['busline']) ? $enroll['busline'] : ''; ?></textarea> 
      </div>
-->
      <div class="form-group">
        <label>内容拓展数据</label>
        <div class="row" style="margin-top:0;">
          <span class="col-sm-1"></span>
          <div class="row col-sm-10" id="extdata">
          <?php foreach ($extcolumn as $column): ?>

            <div class="extdata">
              <div class="form-inline">
                <div class="form-group">
                  <label for="extkey">字段key</label>
                  <input type="text" class="form-control" name="extkey" value="<?php echo $column['key']; ?>" placeholder="字段key，保存的key">
                </div>
                <div class="form-group" style="margin-left: 20px">
                  <label for="extname">字段名</label>
                  <input type="text" class="form-control" name="extname" value="<?php echo $column['name']; ?>" placeholder="字段名，用户看到的">
                </div>

              <div class="form-group" style="margin-left: 20px">
                <label for="extrange">选项</label>
                <input type="text" class="form-control" name="extrange" value='<?php echo isset($column['range']) ? json_encode($column['range']) : '[]'; ?>' placeholder='json格式([{"name":"name","val":"val"}])，由开发填写'>
              </div>  

                <button type="submit" class="btn btn-default btn-delete" style="margin-left: 10px">删除</button>
              </div>          
            </div>
          <?php endforeach ?>
          </div>
        </div>
      </div>

      <div class="form-group">
        <span class="col-sm-2"></span>
        <div class="col-sm-10">
          <button type="button" class="btn" id="add-btn">添加</button>
        </div>
      </div>


      <div class="form-group">
        <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>
      </div>
    </form>

    <div class="extdata-template" style="display: none">
      <div class="form-inline">
        <div class="form-group">
          <label for="extkey">字段key</label>
          <input type="text" class="form-control" name="extkey" value="" placeholder="字段key，保存的key">
        </div>
        <div class="form-group" style="margin-left: 20px">
          <label for="extname">字段名</label>
          <input type="text" class="form-control" name="extname" value="" placeholder="字段名，用户看到的">
        </div>
        <div class="form-group" style="margin-left: 20px">
          <label for="extrange">选项</label>
          <input type="text" class="form-control" name="extrange" value="" placeholder="json格式，由开发填写">
        </div>        
        <button type="submit" class="btn btn-default btn-delete" style="margin-left: 10px">删除</button>
      </div>    
    </div>  

  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
  var is_uploading = false;
  var serialize_url = '';

  var all_input_name = [];
  $('#form1 input[name]').each(function(){
    if($(this).attr('name')) {
      all_input_name.push($(this).attr('name'))
    }
  });


  $("#add-btn").click(function() {
    $('#extdata').append('<div class="extdata">' + $('.extdata-template').last().html() + '</div>');
    deleteEvent();
  });

  deleteEvent();

  function deleteEvent() {
    $(".btn-delete").click(function() {
      $(this).parent().parent().remove();
    });
  }

 var ext_data = [];

  $('#_submit-btn').click(function(){
    if (get_uploading_status()) {
      submit_alert('正在上传图片，请稍后...');
      return false;
    }
    var is_post = true;
   
    var now_key = '';
    $('.extdata').each(function(i, obj){
      if (!is_post) {
        return false;
      }

      ext_data[i] = {};

      $(this).find('*').each(function(j, ele){
        if ($(ele).attr('name')) {
          if (!$(ele).val()) {
            console.log($(ele));
            is_post = false;
            submit_alert('拓展数据任一字段不能为空');
            return false;
          }
          if ($(ele).attr('name') == 'extkey') {
            if (all_input_name.includes($(ele).val())) {
              is_post = false;
              submit_alert('拓展数据字段key和已有字段冲突，请尽量用下划线开头定义');
              return false;
            }
            if(! /^([a-zA-Z_]{1,}[a-zA-Z_0-9]{0,})$/.test($(ele).val())) {
              is_post = false;
              submit_alert('拓展数据字段key必须以英文字母或者下划线开头，只能是英文数字下划线组合');
              return false;
            } else {
              now_key = $(ele).val();
              ext_data[i].key = now_key;
            }
          }
          if ($(ele).attr('name') == 'extname') {
            ext_data[i].name = $(ele).val();
          }
          if ($(ele).attr('name') == 'extrange') {
            ext_data[i].range = $(ele).val();
          }
          is_post = true;
        }
      });
    });

    if(!is_post) {
      return;
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

    if(ext_data.length>0) {
      data += '&extdata=' + JSON.stringify(ext_data);
    }

    $.ajax({
      type: "POST",
      url: "index.php?mod=enroll&action=post_enroll&wants_json=true",
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