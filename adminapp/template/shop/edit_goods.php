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
  .form-control,.bg-success {
    width: 305px;
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
  <p></p>
  <div>
    <form id="form1"  class="form-horizontal" action="" name="form" enctype="multipart/form-data">

      <input type="hidden" name="id" value="<?=isset($info['id']) ? $info['id'] : ''; ?>">

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">商品名称</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="name"  value="<?=isset($info['name']) ? $info['name'] : ''?>">
        </div>
      </div>
      
      <div class="form-group">
        <label for="" class="col-sm-1 control-label">商品类型</label>
        <div class="col-sm-7">
          <select class="form-control" name="type" id="type">
            <option value="">请选择</option>
            <?php foreach ($type_list as $type) { ?>
            <option value="<?=$type['id']; ?>" <?=!empty($info) && $info['type']==$type['id']  ? 'selected' : ''; ?>><?=$type['name']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">是否上架</label>
        <div class="col-sm-7">
          <label class="radio-inline">
            <input type="radio" name="is_online" value="1" <?=!empty($info) && $info['is_online'] ? 'checked' : ''; ?>> 是
          </label>
          <label class="radio-inline">
            <input type="radio" name="is_online" value="0" <?=!empty($info) && !$info['is_online'] ? 'checked' : ''; ?>> 否
          </label>      
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">排序</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="sort_order"  value="<?=isset($info['sort_order']) ? $info['sort_order'] : ''?>">
          <p class="bg-success">
            <span class="help-block">填写数值越大越排前，取值范围为0到255</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">限购</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="limit"  value="<?=isset($info['limit']) ? $info['limit'] : ''?>">
          <p class="bg-success">
            <span class="help-block">限制每单购买个数，0为不限制</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">商品价格</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="price"  value="<?=isset($info['price']) ? $info['price'] : ''?>">
          <p class="bg-success">
            <span class="help-block">单位 元，支持两位小数</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="" class="col-sm-1 control-label">商品原价</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="orig_price"  value="<?=isset($info['orig_price']) ? $info['orig_price'] : ''?>">
          <p class="bg-success">
            <span class="help-block">不填写时前台不显示</span>
          </p>
        </div>
      </div>

<?php if (empty($info)): ?>
      <div class="form-group">
        <label for="" class="col-sm-1 control-label">商品数量</label>
        <div class="col-sm-7">
          <input class="form-control" type="text" name="count"  value="<?=isset($info['count']) ? $info['count'] : ''?>">
          <p class="bg-success">
            <span class="help-block">填写库存量</span>
          </p>
        </div>
      </div> 
<?php endif ?>

      <div class="form-group">
        <label class="col-sm-1 control-label">缩略图上传</label>
        <div class="col-sm-7">
          <input type="file" class="form-control fileupload" name="cover_pic" value="" data-uped="<?php echo isset($info['cover_pic']) && $info['cover_pic'] ? 1 : 0; ?>">
          <p class="bg-success">
            <span class="help-block">图片尺寸：190×190，图片大小：100k</span>
            <?php if (isset($info['cover_pic']) && $info['cover_pic']): ?>
            <span>
              <a href="<?php echo $info['cover_pic']; ?>" target="_blank">查看已上传图片</a>
            </span>
            <?php endif ?>
          </p>
        </div>      
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">商品图上传</label>
        <div class="col-sm-7">
          <input type="file" class="form-control fileupload" name="goods_pic" value="" data-uped="<?php echo isset($info['goods_pic']) && $info['goods_pic'] ? 1 : 0; ?>">
          <p class="bg-success">
            <span class="help-block">图片尺寸：420×420，图片大小：300k</span>
            <?php if (isset($info['goods_pic']) && $info['goods_pic']): ?>
            <span>
              <a href="<?php echo $info['goods_pic']; ?>" target="_blank">查看已上传图片</a>
            </span>
            <?php endif ?>
          </p>
        </div>      
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">购买须知</label>
        <div class="col-sm-7">
          <textarea class="form-control" cols="50" rows="6" name="notice" id="notice"><?php echo !empty($info) ? $info['notice'] : ''; ?></textarea>
          <p class="bg-success">
            <span class="help-block">显示于详情页，商品名下方</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">商品简述</label>
        <div class="col-sm-7">
          <textarea class="form-control" cols="50" rows="6" name="summary" id="summary"><?php echo !empty($info) ? $info['summary'] : ''; ?></textarea>
          <p class="bg-success">
            <span class="help-block">在首页页商品列表，鼠标移至商品时显示</span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">商品描述</label>
        <div class="col-sm-7">
          <textarea class="form-control" cols="50" rows="6" name="remark" id="remark"><?php echo !empty($info) ? $info['remark'] : ''; ?></textarea>
          <p class="bg-success">
            <span class="help-block">详情页下半部分，<span style="color:red;">图片宽度不能大于900！ </span></span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <span class="col-sm-1"></span>
        <div class="col-sm-10">
          <button type="button" class="btn btn-primary" name="editsubmit" id="_submit-btn">提交</button>  
        </div>
      </div>

    </form>
  </div>
</div>
<?php include(APP_ROOT . '/template/common/footer.php'); ?>
<script type="text/javascript">
  var editor;
  KindEditor.ready(function(K) {
    editor = K.create('textarea[name="remark"]', {
      resizeType : 1,
      allowPreviewEmoticons : false,
      allowImageUpload : true,
      afterBlur:function(){
          this.sync();
      },          
      items : [
        'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
        'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
        'insertunorderedlist', '|', 'image', 'link'],
      uploadJson : 'index.php?mod=shop&action=editor_upload',
      allowFileManager : false,
      height : '500px',
      width : '800px',
    });

    editor2 = K.create('textarea[name="notice"]', {
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
</script>
<script type="text/javascript">
$('.fileupload').click(function(){
  var uped = $(this).attr('data-uped');
  if(parseInt(uped)===1){
    alert('已上传图片，再次上传将覆盖原上传图片！');
  }
});


var request;
var loading = false;
$("#_submit-btn").click(function() {
  var name = $("input[name='name']").val();
  if(!name){
    alert('请填写商品名称！');
    $("input[name='name']").focus();
    return false;
  }
  // if(name.length>12){
  //  alert('商品名称不能超过12个字符！');
  //  $("input[name='name']").focus();
  //  return false;
  // }
  var type = $("select[name='type']").val();
  if(!type){
    alert('请选择商品类型！');
    $("select[name='type']").focus();
    return false;
  }
  var is_online = $('input[name="is_online"]:checked').val();
  if(typeof(is_online)=='undefined'){
    alert('请选择是否上架！');
    $("input[name='is_online']")[0].focus();
    return false;
  }
  var price = parseInt($("input[name='price']").val());
  if(!price){
    alert('请填写商品价格！');
    $("input[name='price']").focus();
    return false;
  }

  var orig_price = $("input[name='orig_price']").val();
  if(parseInt(orig_price)>0 && orig_price<=price){
    if(!confirm('价格不比原价低，确定提交吗？')){
      $("input[name='orig_price']").focus();
      return false;
    }
  }

  var cover_pic  = $("input[name='cover_pic']").val();
  var cover_uped = parseInt($("input[name='cover_pic']").attr('data-uped'));
  if(!cover_pic && !cover_uped){
    alert('请上传缩略图！');
    $("input[name='cover_pic']").focus();
    return false;
  }
  var goods_pic  = $("input[name='goods_pic']").val();
  var goods_uped = parseInt($("input[name='goods_pic']").attr('data-uped'));
  if(!goods_pic && !goods_uped){
    alert('请上传商品图！');
    $("input[name='goods_pic']").focus();
    return false;
  }

  if (loading) {
    submit_alert('数据正在提交，请稍后。。。');
    return false;
  }
  loading = true;
  var formElement = document.querySelector("form");
  request = new XMLHttpRequest();
  request.onreadystatechange=state_Change;
  request.open("POST", "index.php?mod=shop&action=update_goods");
  request.send(new FormData(formElement));
});

function state_Change(){
  if (request.readyState == 4) {// 4 = "loaded"
    if (request.status == 200) {// 200 = OK
      console.log(request.responseText);
      var res = JSON.parse(request.responseText);
      if (res.code == 200) {

        submit_confirm(res.message, 'index.php?mod=shop&action=goods', 1);
      } else {
        submit_alert(res.message);
      }
    } else {
      alert("Problem retrieving XML data");
    }
    loading = false;
  }
}
</script>
    </body>
</html>