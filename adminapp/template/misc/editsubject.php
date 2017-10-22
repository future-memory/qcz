<?php include(APP_ROOT . '/template/common/header.php'); ?>
<style type="text/css">
  form {
    margin-top: 20px;
  }
  .row {
    margin-left: 0px;
    margin-right: 0px;
  }
  .width305{width: 305px;}
</style>
<div class="container-fluid">
  <p></p>
  <div class="row">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="index.php?mod=misc">主题列表</a></li>
      <li role="presentation" class="active"><a href="#"><?php echo $info ? '编辑' : '添加'; ?>主题</a></li>
    </ul>
  </div>
  <div class="row">
    <form id="form1" data-return-url="index.php?mod=misc" data-force-return="1" class="form-horizontal" action="index.php?mod=misc&action=post_subject" name="form" enctype="multipart/form-data">
      <input type="hidden" value="<?=isset($info['key']) ? $info['key'] : ''?>" name="key" id="key">

      <div class="form-group">
        <label for="subject_key" class="col-sm-2 control-label">key</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="subject_key" id="subject_key" required placeholder="" value="<?=isset($info['key']) ? $info['key'] : ''?>">
        </div>
      </div>  

      <div class="form-group">
        <label for="name" class="col-sm-2 control-label">主题名称</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="name" id="name" required placeholder="" value="<?=isset($info['name']) ? $info['name'] : ''?>">
        </div>
      </div> 

      <div class="form-group">
        <label for="show_count" class="col-sm-2 control-label">显示条数</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="show_count" id="show_count" required placeholder="" value="<?=isset($info['show_count']) ? $info['show_count'] : ''?>">
        </div>
      </div>

      <div class="form-group">
      	<label class="col-sm-2 control-label">需要过期</label>
      	<div class="col-sm-10">
					<label class="radio-inline">
					  <input type="radio" name="expire" value="1" <?= isset($info['expire']) && $info['expire'] ? 'checked' : ''?>> 是
					</label>
					<label class="radio-inline">
					  <input type="radio" name="expire" value="0" <?= isset($info['expire']) && !$info['expire'] ? 'checked' : ''?>> 否
					</label>      	
      	</div>
      </div>

      <div class="form-group">
      	<label class="col-sm-2 control-label">显示图片</label>
      	<div class="col-sm-10">
					<label class="radio-inline">
					  <input type="radio" name="show_pic" value="1" <?= isset($info['show_pic']) && $info['show_pic'] ? 'checked' : ''?>> 是
					</label>
					<label class="radio-inline">
					  <input type="radio" name="show_pic" value="0" <?= isset($info['show_pic']) && !$info['show_pic'] ? 'checked' : ''?>> 否
					</label>      	
      	</div>
      </div>

      <div class="form-group">
        <label for="pic_width" class="col-sm-2 control-label">图片宽(px)</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="pic_width" id="pic_width" required placeholder="" value="<?=isset($info['pic_width']) ? $info['pic_width'] : ''?>">
        </div>
      </div>

      <div class="form-group">
        <label for="pic_height" class="col-sm-2 control-label">图片高(px)</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="pic_height" id="pic_height" required placeholder="" value="<?=isset($info['pic_height']) ? $info['pic_height'] : ''?>">
        </div>
      </div>

      <div class="form-group">
        <label for="pic_size" class="col-sm-2 control-label">图片大小(K)</label>
        <div class="col-sm-10">
          <input type="text" class="form-control width305" name="pic_size" id="pic_size" required placeholder="" value="<?=isset($info['pic_size']) ? $info['pic_size'] : ''?>">
        </div>
      </div>

      <div class="form-group">
      	<label class="col-sm-2 control-label">随机显示</label>
      	<div class="col-sm-10">
					<label class="radio-inline">
					  <input type="radio" name="random" value="1" <?= isset($info['random']) && $info['random'] ? 'checked' : ''?>> 是
					</label>
					<label class="radio-inline">
					  <input type="radio" name="random" value="0" <?= isset($info['random']) && !$info['random'] ? 'checked' : ''?>> 否
					</label>      	
      	</div>
      </div>

      <div class="form-group">
      	<label class="col-sm-2 control-label">需要定时开始</label>
      	<div class="col-sm-10">
					<label class="radio-inline">
					  <input type="radio" name="start_time" value="1" <?= isset($info['start_time']) && $info['start_time'] ? 'checked' : ''?>> 是
					</label>
					<label class="radio-inline">
					  <input type="radio" name="start_time" value="0" <?= isset($info['start_time']) && !$info['start_time'] ? 'checked' : ''?>> 否
					</label>      	
      	</div>
      </div>

      <div class="form-group">
      	<label class="col-sm-2 control-label">说明</label>
      	<div class="col-sm-10">
        	<textarea class="form-control" name="tips" rows="6" id="textarea"><?=isset($info['tips']) ? $info['tips'] : ''; ?></textarea>	
      	</div>
      </div>
      <div class="row">
        <label class="col-sm-2 control-label">内容拓展数据</label>
        <div class="col-sm-10">
          <div class="row" id="extdata">
          <?php foreach ($extdata_array['keys'] as $_v_key): ?>
            <div class="extdata">
              <div class="form-inline">
                <div class="form-group">
                  <label for="extkey">字段key</label>
                  <input type="text" class="form-control" name="extkey" value="<?=$_v_key?>" placeholder="APP用">
                </div>
                <div class="form-group" style="margin-left: 20px">
                  <label for="extvalue">字段key别名</label>
                  <input type="text" class="form-control" name="extvalue" value="<?=$extdata_array['k_v'][$_v_key]?>" placeholder="方便记忆，用中文亦可">
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
        <span class="col-sm-2"></span>
        <div class="col-sm-10">
          <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">
          	<?php if (isset($info['key'])): ?>
          		更新
          	<?php else: ?>
          		新增
          	<?php endif ?>
          </button>
        </div>
      </div>
    </form>
    <div class="extdata-template" style="display: none">
      <div class="form-inline">
        <div class="form-group">
          <label for="extkey">字段key</label>
          <input type="text" class="form-control" name="extkey" value="" placeholder="APP用">
        </div>
        <div class="form-group" style="margin-left: 20px">
          <label for="extvalue">字段key别名</label>
          <input type="text" class="form-control" name="extvalue" value="" placeholder="方便记忆，用中文亦可">
        </div>
        <button type="submit" class="btn btn-default btn-delete" style="margin-left: 10px">删除</button>
      </div>    
    </div>    
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">

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
  $('#_submit-btn').click(function(){
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
              extdata[i][$(ele).val()] = '';
            }
          }
          if ($(ele).attr('name') == 'extvalue') {
            extdata[i][now_key] = $(ele).val();
            now_key = '';
          }
          is_post = true;
        }
      });
    });
    if (is_post) {
      post_data();
    }

    function post_data() {
      var data = $('#form1').serialize();
      if (extdata[0]) {
        data += '&extdata=' + JSON.stringify(extdata);
      }
      $.ajax({
        type: "POST",
        url: "index.php?mod=misc&action=post_subject&wants_json=true",
        data: data,
        dataType: 'json',
        error: function(request) {
            submit_alert("错误，请重试");
        },
        success: function(res) {
          if(res && res.code==200){
            submit_confirm(res.message, $('#form1').attr('data-return-url'), 1);
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