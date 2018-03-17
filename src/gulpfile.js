'use strict';

// Include gulp
var gulp = require('gulp');
var path = require('path');
// var sass = require('gulp-sass');
var browserSync = require('browser-sync').create();
var rev = require('gulp-rev');
var watchPath = require('gulp-watch-path');
var named = require('vinyl-named');
var del = require('del');
// var watch = require('gulp-watch');
// var revReplace = require('gulp-rev-replace');
// var merge = require('merge-stream');
var replace = require('gulp-replace');
var through = require('through2');
var gutil = require('gulp-util');
var base64 = require('gulp-base64');
var revOutdated = require('gulp-rev-outdated');
var useref = require('gulp-useref');
// var cdn = require('gulp-cdn');
var runSequence = require('run-sequence');
var less = require('gulp-less');
var gif = require('gulp-if');
var cssnano = require('gulp-cssnano');
var uglify = require('gulp-uglify');
var autoprefixer = require('autoprefixer');
// var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
// var imagemin = require('gulp-imagemin');
var inline = require('gulp-inline-source');

var ExtractTextPlugin = require('extract-text-webpack-plugin');
var webpackStream = require('webpack-stream');
var webpack = require('webpack');

var paths = require('./gulpfile_config');
var webpackConfig = require('./webpack.config');

/**
 ** usage:   gulp serve 开发模式，自动刷新和文件刷新
 **          gulp build_dist  发布，文件版本和压缩
 **/

// dev启动
// 1.编译移动页面到public          OK
// 2.编译scss 输出到public         OK
// 3.编译js文件 输出public         OK
// 4.编译组件                      OK
// 5.输出图片和字体文件             OK
// 6.监听所有类型文件执行不同task    OK

// build
// 编译 压缩 css
// 编译 压缩 js
// 移动 图片和字体


// util function
function compileJS(path, dest) { //使用webpack来编译 js ， 包括vue和es6
  dest = dest || './static_new/';
  // webpackConfig.output.publicPath = BUILD === 'PUBLIC' ? '' + CDN + '/' : '/ss/';
  if (BUILD === 'pubilc') {
    delete webpackConfig.devtool;
    // console.log(webpackConfig)
  }

  return gulp.src(path)
    .pipe(named(function(file) {
      var path = JSON.parse(JSON.stringify(file)).history[0];
      var sp = path.indexOf('\\') > -1 ? '\\js\\' : '/js/';
      var target = path.split(sp)[1];
      return target.substring(0, target.length - 3);
    }))
    .pipe(webpackStream(webpackConfig))
    .on('error', function(err) {
      this.end()
    })
    .pipe(browserSync.reload({
      stream: true
    }))
    .pipe(gulp.dest(dest))
}

function cp(from, to) {
  gulp.src(from)
    .pipe(gulp.dest(to));
}

//------------------------------dev----------------------------

var BUILD = "DEV";

//自动刷新及文件监听
gulp.task('dev', function() {
  // console.log(webpackConfig);
  webpackConfig.plugins.push(new webpack.DefinePlugin({
    NODE_ENV: JSON.stringify(process.env.NODE_ENV) || 'dev'
  }));

  runSequence(['css', 'js', 'img', 'less', 'html'], function() {
    // browserSync();
    browserSync.init({
      proxy: 'bbs-app.meizu.cn/',
      port: 8080,
      open: true,
      notify: false,
      // files: ['./template_src/**/*.htm'],
    });

    dev();
    // gulp.watch(paths.src.less, ['less']);
    // gulp.watch(paths.src.html, ['html']);
    // gulp.watch(paths.src.css, ['css']);
    // gulp.watch(paths.src.js, ['js']);
    // gulp.watch(paths.src.img, ['img']);
  });

});

//编译 less
gulp.task('less', function() {
  return gulp.src(paths.src.less)
    .pipe(less({ relativeUrls: true }))
    .on('error', function(error) {
      console.log(error.message);
    })
    // .pipe(lazyImageCSS({imagePath: lazyDir}))
    .pipe(gulp.dest(paths.dev.css))
    // .on('data', function () {
    // })
    // .on('end', function() {
    //   browserSync.stream();
    // })
})

//编译sass
// gulp.task('sass', function() {
//   return gulp.src(paths.src.sass)
//     .pipe(sourcemaps.init())
//     .pipe(sass().on('error', sass.logError))
//     .pipe(sourcemaps.write(paths.dev.css))
//     .pipe(gulp.dest(paths.dev.css))
//     // .on('end', function() {
//     //   browserSync.stream();
//     // })
//     // .pipe(gulp.dest('./public/css'));
// });

gulp.task('css', function() {
  return gulp.src(paths.src.css)
    .pipe(gulp.dest(paths.dev.css))
    // .pipe(browserSync.stream());
})

gulp.task('html', function() {
  return gulp.src(paths.src.html)
    .pipe(gulp.dest(paths.dev.html))
    // .on('end', function() {
    //   browserSync.reload();
    // })
})

gulp.task('js', function() {
  cp('./static_src/js/lib/*.js', './static_new/js/lib');
  return compileJS(['./static_src/js/*/*.js', '!./static_src/js/lib/*.js']); //只编译js首层目录的js文件，子目录js文件通过import进首层目录的js文件中
})

function dev() {
  gulp.watch([paths.src.html]).on('change', function() {
    runSequence('html', function() {
      browserSync.reload();
    });
  });

  // gulp.watch([paths.src.sass]).on('change', function() {
  //   runSequence('sass', function() {
  //     browserSync.reload();
  //   });
  // });

  gulp.watch([paths.src.less]).on('change', function() {
    runSequence('less', function() {
      browserSync.reload();
    });
  });

  gulp.watch([paths.src.css]).on('change', function() {
    runSequence('css', function() {
      browserSync.reload();
    });
  });

  gulp.watch([paths.src.js], function(event) {
    var watch_paths = watchPath(event, paths.src.js, paths.dev.js); //会获得改变的js的文件路径
    var sp = watch_paths.srcPath.indexOf('\\') > -1 ? '\\' : '/'; //兼容windows路径分隔符
    var dirs = event.path.split(sp); //以路径分隔符/，划分的数组
    var business = dirs[dirs.indexOf('js') + 1]; //要做的很简单，就是获取js目录下的各个首层目录名，如js/lottery  得到lottery

    var path = './static_src/js/' + business + '/*.js'; //只编译 模块目录的根文件，一些功能函数和filter建立相应的子目录，通过import进根文件
    compileJS(path);

    // if (watch_paths.srcPath.split(sp).length === 3) { // 共有库(在js根目录下的文件)情况,要编译所有js
    //   compileJS([paths.src.js, '!./static_src/js/lib/*.js']);
    // } else { // 否则 只编译变动js
    //
    // }
  });


  gulp.watch(['./static_src/components/**/*.vue'], function(event) {
    var sp = event.path.indexOf('\\') > -1 ? '\\' : '/';
    var business = event.path.split(sp).slice(-2); //获取路径分隔符分割的后2个字段， 如/static_new/components/home/home.vue 得到[home, home.vue]
    // var jsFile = business[1].split('-')[0];
    var path;
    if (business[0] === 'common') { //如果组件是在common目录下，就是共用的，就会编译所有js
      path = [paths.src.js, '!./src/js/lib/*.js'];
    } else {
      path = './static_src/js/' + business[0] + '/*.js'; //如果不是共用组件，就只编译对应页面下的组件。每个/js/**/*.js其实是对应页面模块的入口
    }


    compileJS(path);
  })
}

//------------------------------dev end----------------------------



//-----------------------------dist--------------------------------

gulp.task('img', function() {
  return gulp.src(paths.src.img)
    .pipe(gulp.dest(paths.dev.img))
});

//清除多余的hash文件 clean:rev依赖
function cleaner() {
  return through.obj(function(file, enc, cb) {
    rimraf(path.resolve((file.cwd || process.cwd()), file.path), function(err) {
      if (err) {
        this.emit('error', new gutil.PluginError('Cleanup old files', err));
      }
      this.push(file);
      cb();
    }.bind(this));
  });
}

//只保留一个最新版本的rev
gulp.task('clean:rev', function() {
  return gulp.src(paths.dist.static)
    .pipe(revOutdated(1))
    .pipe(cleaner());
})

//加版本号
gulp.task('rev', function() {
  return gulp.src([paths.dev.static, '!' + paths.dev.static + 'img/**/*', '!' + paths.dev.static + 'js/**/*.map'])
    .pipe(rev())
    .pipe(gif('*.css',
      // autoprefixer({ browsers: ['last 2 version', 'safari 5', 'opera 12.1', 'ios 6', 'android 4', '> 10%'] }),
      cssnano({ safe: true, autoprefixer: false })
    )) //压缩css
    .pipe(gif('*.js', uglify())) //压缩js
    .pipe(gulp.dest(paths.dist.static))
    .pipe(rev.manifest())
    .pipe(gulp.dest(paths.dist.static));
})

//移动图片
gulp.task('copyImg', function() {
  return gulp.src(paths.dev.img + "**/*")
    .pipe(gulp.dest(paths.dist.img))
})

//合并css, js
/**  usage
    <!-- build:css ../../../static_new/mobile/css/home/build.css -->  不知道这里为什么是../../../而不是../../../../
    <link rel="stylesheet" type="text/css" href="../../../../static_new/mobile/css/home/common.css" />
    <link rel="stylesheet" type="text/css" href="../../../../static_new/mobile/css/home/home.css" />
    <!-- endbuild -->

    会被编译成  <link rel="stylesheet" href="../../../static_new/mobile/css/home/build.css">
**/
gulp.task('useref', function() {
  return gulp.src(paths.dist.html + '**/*.php')
    .pipe(useref())
    .pipe(replace(paths.pathReg, function(match, g1, g2, g3) { //因为useref生产的合并文件是以gulpfile.js所在目录为基准的，所以模板中生成的相对路径不对，予以修正
      console.log(match + '  ' + g3)
      return '../' + match;
    }))
    .pipe(gulp.dest(paths.dist.html)); //修改后的html file保存目录
})


gulp.task('inline', function() { //inline之前要将useref生成的相对路径改正，才能正常inline
  return gulp.src(paths.dist.html + '**/*.php')
    .pipe(inline())
    .pipe(gulp.dest(paths.dist.html));
})

//替换模板文件 的外链资源的版本号和cdn路径
gulp.task('rev:html', function() {
  var cdn = '//res.shop.com/'
  var s = require(paths.dist.static + 'rev-manifest.json');

  gulp.src(paths.dist.html + '**/*')
    .pipe(replace(paths.pathReg, function(match, g1, g2, g3) {
      //g2第二个分组为需要的key，如css/home/home.css
      if (s[g3]) {
        return cdn + s[g3];
      } else return match;
    }))

  //替换inline css中的相对路径
  .pipe(replace(/(\.\.\/)+/g, '//res.shop.com/'))
    .pipe(gulp.dest(paths.dist.html));
});


function build(cb) {
  runSequence(
    ['css', 'js', 'img', 'less', 'html'],
    'useref',
    'inline',
    'rev',
    'clean:rev',
    'rev:html',
    'copyImg',
    function() {
      // 上传静态资源文件到CDN
      cb && cb();
    });
}

gulp.task('build', function() {
  BUILD = 'pubilc';
  webpackConfig.plugins.push(new webpack.DefinePlugin({
    NODE_ENV: JSON.stringify(process.env.NODE_ENV) || 'production'
  }));

  build();
})


//-----------------------------dist end----------------------------