/**
 comon forum submit.
 form's id need to be #forum1
 button's id need to by #submit-btn
**/

function submit_confirm(msg, returl, force_return){
    force_return = typeof(force_return)=='undefined' ? 0 : force_return;
    if (force_return == 1) {
        bootbox.alert(msg, function(){
            window.location.href = returl;
        });
    } else {
        bootbox.confirm({ 
          buttons: {  
              confirm: {  
                  label: '返回',  
                  className: 'btn-myStyle'  
              },  
              cancel: {  
                  label: '留下',
                  className: 'btn-default'  
              }  
          },
          message: msg,  
          callback: function(result) {  
              if(result && returl) {  
                  window.location.href = returl;  
              }
          }
        });
    }
}

function submit_alert(msg){
    bootbox.alert({  
        buttons: {  
           ok: {  
                label: '确定',  
                className: 'btn-myStyle'  
            }  
        },  
        message: (msg ? msg : '提交成功'),  
        callback: function() {  
        }
    });
}

function submit_hint(msg, returl, hint) {
    bootbox.alert({  
        buttons: {  
           ok: {  
                label: hint,  
                className: 'btn-myStyle'  
            }  
        },  
        message: msg,  
        callback: function() {
            window.location.href = returl;
        }
    });
}

$('#submit-btn').click(function(){
  var url    = $('#form1').attr('action');
  var data   = $('#form1').serialize();
  var returl = $('#form1').attr('data-return-url');
  var force_return    = $('#form1').attr('data-force-return');

  $.ajax({
    type: "POST",
    url: url + "&wants_json=true",
    data: data,
    dataType: 'json',
    error: function(request) {
        submit_alert("错误，请重试");
    },
    success: function(res) {
    	if(res && res.code==200 && returl){
            submit_confirm(res.message, returl, force_return);
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
});
