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
  .form-control,.bg-success {
    width: 305px;
  }

</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=misc">主题列表</a></li>
      <li role="presentation"><a href="index.php?mod=misc&action=edit_subject">添加主题</a></li>
      <li role="presentation" class="active"><a href="#">内容管理 <?=$info['name']?></a></li>
    </ul>
  </div>
  <div class="row">
  主题：<?=$info['name']?>
  </div>
  <div class="row">
    <table class="table table-striped">
      <thead>
        <tr>
          <th width="80px">排序</th>
          <th width="300px">标题</th>
          <th>链接</th>
          <th width="80px">图片</th>
          <th width="80px">环境</th>
          <th width="100px">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $item): ?>
        <tr>
          <?php $picurl = $item['pic'] ? HelperUtils::get_pic_url($item['pic']) : null; ?>
          <td><?=$item['order']?></td>
          <td><?=$item['title']?></td>
          <td>
            <a href="<?=$item['url']?>"><?=$item['url'] ? $item['url'] : '-'?></a>
          </td>
          <td>
            <?=$picurl ? '<a href="#" onclick="return false;" class="show_pic" data-id="'.$item['id'].'"><span>显示</span><span style="display:none;">隐藏</span></a>' : '-'?>
          </td>
          <td>
            <?php if (in_array($key, $this->gray_keys)): ?>
            <?=$item['envirnment'] == 1  ? '灰度' : '线上'?>
            <?php endif ?>
          </td>
          <td>
            <a href="index.php?mod=misc&action=edit_data&id=<?=$item['id']?>&key=<?=$key?>">编辑</a>&nbsp;&nbsp;
            <a href="index.php?mod=misc&action=del_data&id=<?=$item['id']?>&key=<?=$key?>">删除</a>&nbsp;&nbsp;
          </td>
        </tr>
        <?php if ($picurl): ?>
        <tr style="display: none" id="tr-<?=$item['id']?>">
          <td colspan="7">
            <img style="max-height:200px;" id="img-<?=$item['id']?>" src="<?=$picurl?>"/>
          </td>
        </tr>
        <?php endif ?>
        <?php endforeach?>
      </tbody>
    </table>
  </div>
  <div class="row">
  添加内容
  </div>
  <div class="row" style="padding: 0 30px; margin-top: 0">
    <form id="form1" data-return-url="/index.php?mod=misc" data-force-return="1" class="form-horizontal" method="post" action="index.php?mod=misc&action=post_data" name="form" enctype="multipart/form-data">
      <input type="hidden" value="<?=isset($info['key']) ? $info['key'] : ''?>" name="key" id="key">

      <div class="form-group">
        <label for="title">标题</label>
        <input type="text" name="title" id="title" class="form-control" required placeholder="" value="">
      </div>

      <div class="form-group">
        <label for="url">链接</label>
        <input type="text" name="url" id="url" class="form-control" required placeholder="" value="">
      </div>

      <?php if ($info['expire']): ?>
      <div class="form-group">
        <label for="expire">过期时间</label>
        <input type="text" name="expire" id="expire" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="">
      </div>              
      <?php endif ?> 

      <?php if ($info['start_time']): ?>
      <div class="form-group">
        <label for="start_time">开始时间</label>
        <input type="text" name="start_time" id="start_time" class="form-control" onfocus="WdatePicker({skin: 'twoer', dateFmt:'yyyy-MM-dd HH:mm:ss'})" value="">
      </div>              
      <?php endif ?>

      <?php if ($info['show_pic']): ?>
      <div class="form-group">
        <label for="pic">上传图片</label>
        <input type="file" name="pic" id="pic" class="form-control" value="">
        <?php if ($info['pic_width'] || $info['pic_height']): ?>
        <p>
        <div class="bg-success" style="">
          <span class="text-danger">图片尺寸：宽<?=$info['pic_width']?>*高<?=$info['pic_height']?>，图片大小：<?=$info['pic_size']?>K
          </span>
        </div>
        </p>
        <?php endif ?>
      </div>        
      <?php endif ?>

      <div class="form-group">
        <label for="order">排序</label>
        <input type="text" name="order" id="order" class="form-control" placeholder="" value="">
        <p class="bg-success">
          <span class="help-block">填写数值越大越排前，取值范围为0到255</span>
        </p>
      </div>

      <div class="form-group">
        <label for="envirnment">环境</label>
        <div>
          <label class="radio-inline">
            <input type="radio" name="envirnment" value="0" checked>线上
          </label>
          <label class="radio-inline">
            <input type="radio" name="envirnment" value="1">灰度
          </label>        
        </div>
      </div>

<?php if(isset($extdata_array['keys']) && $extdata_array['keys']){ ?>
      <div class="row">
      内容拓展数据
      </div>
      <hr />
      <div class="row" id="extdata">
        <?php foreach ($extdata_array['keys'] as $v_key): ?>
        <div class="form-group">
          <label for="<?=$v_key?>"><?=$extdata_array['k_v'][$v_key]?></label>
          <input type="text" name="<?=$v_key?>" class="form-control" placeholder="" value="">
        </div>        
        <?php endforeach ?>
      </div>
      <hr />
<?php } ?>

      <div class="form-group">
        <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>
      </div>
    </form>
    <div class="extdata-template" style="display: none">
      <div class="form-inline">
        <div class="form-group">
          <label for="extkey">key</label>
          <input type="text" class="form-control" name="extkey" value="" placeholder="">
        </div>
        <div class="form-group" style="margin-left: 20px">
          <label for="extvalue">value</label>
          <input type="text" class="form-control" name="extvalue" value="" placeholder="">
        </div>
        <button type="submit" class="btn btn-default btn-delete" style="margin-left: 10px">删除</button>
      </div>    
    </div> 
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

  var is_uploading = false;

  var show_pic = null;
  var allow_size = null;
  var allow_pic_width = null;
  var allow_pic_height = null;

  <?php if ($info['show_pic']): ?>
  show_pic = <?=$info['show_pic']?>;
  <?php endif ?>

  <?php if ($info['pic_size']): ?>
  allow_size = <?=$info['pic_size']?>;
  <?php endif ?>

  <?php if ($info['pic_width'] || $info['pic_height']): ?>
  allow_pic_width = <?=$info['pic_width']?>;
  allow_pic_height = <?=$info['pic_height']?>;
  <?php endif ?>

  function check_url(url){
    var str        = url;
    var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
    var objExp     = new RegExp(Expression);
    return objExp.test(str);
  }

  $('.show_pic').click(function(){
    var id = $(this).attr('data-id');
    $('#tr-'+id).toggle();
    $(this).find('span').toggle();
  });


  $('#_submit-btn').click(function(){  

    var title = $("input[name='title']").val();
    if(!title){
      submit_alert('请填写标题！');
      $("input[name='title']").focus();
      return false;
    }

    var url = $("input[name='url']").val();
    if(!url){
      submit_alert('请填写链接！');
      $("input[name='url']").focus();
      return false;
    }
    if(!check_url(url)){
      submit_alert('请填写正确的链接地址！');
      $("input[name='url']").focus();
      return false;
    }   

    var is_post = true;
    var extdata = [];
    var now_key = '';
    $('.extdata').each(function(i, obj){
      if (!is_post) {
        return false;
      }

      extdata[i] = {};

      $(this).find('*').each(function(j, ele){
        if ($(ele).attr('name')) {
          if (!$(ele).val()) {
            is_post = false;
            submit_alert('拓展数据任一字段不能为空');
            return false;
          }
          // $(ele).attr('name', 'ext_data_' + $(ele).val());
          if(! /^([a-zA-Z_]{1,}[a-zA-Z_0-9]{0,})$/.test($(ele).val())) {
            is_post = false;
            submit_alert('拓展数据字段必须以英文字母或者下划线开头，只能是英文数字下划线组合');
            return false;
          }
          if ($(ele).attr('name') == 'extkey') {
            now_key = $(ele).val();
            extdata[i][$(ele).val()] = '';
          }
          if ($(ele).attr('name') == 'extvalue') {
            extdata[i][now_key] = $(ele).val();
            now_key = '';
          }
          is_post = true;
        }
      });
    });

    var file_data;
    if ($('#pic').prop('files')) {
      var file_data = $('#pic').prop('files')[0];     
    }

    if(show_pic && !file_data) {
      submit_alert('图片为空');
      is_post = false;
      return false;
    }

    if (file_data && is_post) {

      if (allow_size && file_data.size > allow_size * 1024) {
        submit_alert('图片尺寸过大');
        return false;
      }

      if (is_uploading) {
        submit_alert('正在上传图片，请稍后...');
        return false;        
      }
      is_uploading = true;

      var form_data = new FormData();
      form_data.append('file', file_data);

      $.ajax({
        type: "POST",
        url: 'index.php?wants_json=true',
        data: 'mod=attachment&action=local_sign&module=misc&filename='+file_data.name,
        dataType: 'json',
        error: function(request) {
          is_uploading = false;
          submit_alert("错误，请重试");
        },
        success: function(res) {
          if(res && res.code==200){

            form_data.append('filepath', res.data.filepath);

            $.ajax({
              url: res.data.url,
              dataType: 'text',
              cache: false,
              contentType: false,
              processData: false,
              data: form_data,                         
              type: 'post',
              error: function(request) {
                is_uploading = false;
                request = JSON.parse(request.responseText);
                submit_alert('上传图片错误: ' + request.error);
              },
              success: function(_res){
                //_res = _res.replace(/image-/g, 'image_');
                _res = JSON.parse(_res);
                if (_res && _res.data && _res.data.filepath) {
                  post_data(_res.data.filepath);
                }else{
                  submit_alert(_res.error);
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
    } else if(is_post){
      post_data();
    }

    function post_data(pic_url = '') {
      var data = $('#form1').serialize();
      if (pic_url) {
        data += '&picurl=' + pic_url;
      }
      if (extdata[0]) {
        data += '&extdata=' + JSON.stringify(extdata);
      }
      $.ajax({
        type: "POST",
        url: "index.php?mod=misc&action=post_data&wants_json=true",
        data: data,
        dataType: 'json',
        error: function(request) {
          is_uploading = false;
          submit_alert("错误，请重试");
        },
        success: function(res) {
          is_uploading = false;
          if(res && res.code==200){
            submit_confirm(res.message, window.location.href, 1);
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
  });
</script>
    </body>
</html>