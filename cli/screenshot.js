var page = require('webpage').create(),
    system = require('system'),
    address, output, size;
 
if (system.args.length < 3 || system.args.length > 5) {
    console.log('Usage: rasterize.js URL filename');
    phantom.exit(1);
} else {
    address = system.args[1];
    output = system.args[2];
    page.viewportSize = {width: 960, height: 800};
    page.open(address, function (status) {
      // 通过在页面上执行脚本获取页面的渲染高度
      var h = page.evaluate(function () { 
        return document.body.scrollHeight; 
      });
      // 按照实际页面的高度，设定渲染的宽高
      page.clipRect = {
        top:    0,
        left:   0,
        width:  960,
        height: h>100 ? h+100 : 800
      };
      // 预留一定的渲染时间
      window.setTimeout(function () {
        page.render(output);
        page.close();
        console.log('ok');
        phantom.exit(1);
      }, 1000);
    });
}
