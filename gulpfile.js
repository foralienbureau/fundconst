//paths for source and bundled parts of app
var basePaths = {
    src: 'src/',
    dest: 'fcon/assets/',
    npm: 'node_modules/'
};

//require plugins 
var gulp = require('gulp'); 

var es          = require('event-stream'),
    gutil       = require('gulp-util'),
    path        = require('relative-path'),
    runSequence = require('run-sequence'),
    uglify      = require('gulp-uglify-es').default,
    del         = require('del'),
    run         = require('gulp-run-command').default,
    imagemin    = require('gulp-imagemin'),
    pngquant    = require('imagemin-pngquant');


//plugins - load gulp-* plugins without direct calls
var plugins = require("gulp-load-plugins")({
    pattern: ['gulp-*', 'gulp.*'],
    replaceString: /\bgulp[\-.]/ 
});

//env - call gulp --prod to go into production mode
var sassStyle = 'expanded'; // SASS syntax
var sourceMap = true; //wheter to build source maps
var isProduction = false; //mode flag

if(gutil.env.prod === true) {
    isProduction = true;
    sassStyle = 'compressed';
    sourceMap = false;
}

//log
var changeEvent = function(evt) {
    gutil.log('File', gutil.colors.cyan(evt.path.replace(new RegExp('/.*(?=/' + basePaths.src + ')/'), '')), 'was', gutil.colors.magenta(evt.type));
};  

//js
gulp.task('build-js', function() {

    var vendorFiles = [],
        appFiles = [basePaths.src+'js/front-*.js']; //our own JS files
        

    return gulp.src(vendorFiles.concat(appFiles)) //join them
        .pipe(plugins.concat('bundle.js'))//combine them into bundle.js
        .pipe(isProduction ? uglify() : gutil.noop()) //minification
        .pipe(plugins.size()) //print size for log
        .on('error', console.log) //log
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
});


gulp.task('build-alpine-js', function() {
    
    //var mask = gulp.src([basePaths.npm + '@alpinejs/mask/dist/cdn.min.js']);
    var core = gulp.src([basePaths.npm + 'alpinejs/dist/cdn.min.js']);
    return es.concat(core)
        .pipe(plugins.concat('alpine.js'))
        .pipe(gulp.dest(basePaths.dest+'js')) //write results into file
        .on('error', console.log); //log
});


//sass
gulp.task('build-css', function() {

    var paths = [];
        //slick = path('./node_modules/slick-carousel/slick')
        //paths.push(slick);

    var appFiles = gulp.src([basePaths.src+'sass/front-main.scss']) //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : gutil.noop())  //process the original sources for sourcemap
        .pipe(plugins.sass({
                outputStyle: sassStyle, //SASS syntas
                includePaths: paths //add bourbon
            }).on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                overrideBrowserslist: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : gutil.noop()) //add the map to modified source
        .on('error', console.log); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('bundle.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : gutil.noop()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', console.log); //log
});


gulp.task('build-editor-css', function() {

    //paths vendor
    var paths = [];

    var appFiles = gulp.src([basePaths.src+'sass/editor-main.scss']) //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : gutil.noop())  //process the original sources for sourcemap
        .pipe(plugins.sass({
                outputStyle: sassStyle, //SASS syntas
                includePaths: paths //add bourbon
            }).on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                overrideBrowserslist: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : gutil.noop()) //add the map to modified source
        .on('error', console.log); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('editor.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : gutil.noop()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', console.log); //log
});


gulp.task('build-admin-css', function() {

    //paths vendor
    var paths = [];

    var appFiles = gulp.src([basePaths.src+'sass/admin-main.scss']) //our main file with @import-s
        .pipe(!isProduction ? plugins.sourcemaps.init() : gutil.noop())  //process the original sources for sourcemap
        .pipe(plugins.sass({
                outputStyle: sassStyle, //SASS syntas
                includePaths: paths //add bourbon
            }).on('error', plugins.sass.logError))//sass own error log
        .pipe(plugins.autoprefixer({ //autoprefixer
                overrideBrowserslist: ['last 4 versions'],
                cascade: false
            }))
        .pipe(!isProduction ? plugins.sourcemaps.write() : gutil.noop()) //add the map to modified source
        .on('error', console.log); //log

    return es.concat(appFiles) //combine vendor CSS files and our files after-SASS
        .pipe(plugins.concat('admin.css')) //combine into file
        .pipe(isProduction ? plugins.csso() : gutil.noop()) //minification on production
        .pipe(plugins.size()) //display size
        .pipe(gulp.dest(basePaths.dest+'css')) //write file
        .on('error', console.log); //log
});


// images
gulp.task('build-images', function (){
    return gulp.src(basePaths.src+'img/**/*.*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()],
            interlaced: true
        }))
        .pipe(gulp.dest(basePaths.dest+'img/'));
});

//fonts
gulp.task('build-fonts', function (){
    return gulp.src(basePaths.src+'fonts/**/*.*')
        .pipe(gulp.dest(basePaths.dest+'fonts'))
});


//revision
gulp.task('revision-clean', function(){
    // clean folder https://github.com/gulpjs/gulp/blob/master/docs/recipes/delete-files-folder.md
    return del([basePaths.dest+'rev/**/*']);
});

gulp.task('revision', function(){

    return gulp.src([basePaths.dest+'css/*.css', basePaths.dest+'js/*.js', basePaths.dest+'svg/*.svg'])
        .pipe(plugins.rev())
        .pipe(gulp.dest( basePaths.dest+'rev' ))
        .pipe(plugins.rev.manifest())
        .pipe(gulp.dest(basePaths.dest+'rev')) // write manifest to build dir
        .on('error', console.log); //log
});


//svg - combine and clear svg assets
gulp.task('svg-opt', function() {

    var icons = gulp.src([basePaths.src+'svg/icon-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeViewBox: false,
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeEditorsNSData: true,
                removeComments: true
            }]
        })) //minification
        .pipe(plugins.cheerio({
            run: function ($) { //remove fill from icons
                $('[fill]').removeAttr('fill');
                $('[fill-rule]').removeAttr('fill-rule');
            },
            parserOptions: { xmlMode: true }
        })),
        pics = gulp.src([basePaths.src+'svg/pic-*.svg'])
        .pipe(plugins.svgmin({
            plugins: [{
                removeViewBox: false,
                removeTitle: true,
                removeDesc: { removeAny: true },
                removeEditorsNSData: true,
                removeComments: true
            }]
        })); //minification

    return es.concat(icons, pics)
        .pipe(plugins.svgstore({ inlineSvg: true })) //combine for inline usage
        .pipe(gulp.dest(basePaths.dest+'svg'));
});

gulp.task('sync', run('sh update.sh'));

//builds
gulp.task('full-build', gulp.series(
    'build-css', 
    'build-admin-css', 
    'build-editor-css', 
    'build-js',
    'build-alpine-js',
    'build-images',
    'build-fonts',
    'svg-opt',
    'revision-clean',
    'revision'));

gulp.task('full-build-css', gulp.series(
    'build-css', 
    'build-admin-css', 
    'build-editor-css', 
    'revision-clean',
    'revision'));

gulp.task('full-build-js', gulp.series(
    'build-js',
    'revision-clean',
    'revision'));

//watchers
gulp.task('watch', function(){

    gulp.watch([basePaths.src+'sass/*.scss', basePaths.src+'sass/**/*.scss'], gulp.series('full-build-css', 'sync'));
    gulp.watch([basePaths.src+'js/*.js', basePaths.src+'gutenberg/*.js'], gulp.series('full-build-js', 'sync'));

});


//default
gulp.task('default', gulp.series('full-build', 'sync', 'watch'));


