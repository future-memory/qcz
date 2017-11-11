'use strict';

//dir代表static(css\js\img等)文件的根目录，  tpdir代表模板文件的根目录
var dir_src = './static_src/';
var tpdir_src = './template_dev/';

var dir_dev = './static_new/';
var tpdir_dev = '../template/';

var dir_dist = '/Users/meizu/MyProject/qcz/res/';
var tpdir_dist = '../template/';

module.exports = {
  src: { //开发目录所有的修改都在这个目录
    less: dir_src + 'css/**/*.less', 
    sass: dir_src + 'css/**/*.scss',
    css: dir_src + 'css/**/*.css',
    js: dir_src + 'js/**/*.js',
    img: dir_src + 'img/**/*',
    html: [tpdir_src + '**/*'],
    vue: dir_src + 'components/**/*.vue'
  },

  dev: { // 浏览器自刷新服务目录，编译后的css、js都在这个目录
    css: dir_dev + 'css/',
    js: dir_dev + 'js/',
    img: dir_dev + 'img/',
    html: tpdir_dev,
    static: dir_dev + '**/*'
  },

  dist: { //发布目录，压缩加版本号的文件都在这个目录
    static: dir_dist,
    html: tpdir_dist,
    img: dir_dist + 'img/'
  },

  pathReg: /((\.\.\/)+)src\/static_new\/(.*(js|css|png|jpg|gif))/g
};