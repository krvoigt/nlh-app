var gulp = require('gulp');
var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var cssnano = require('gulp-cssnano');
var filter = require('gulp-filter');
var newer = require('gulp-newer');
var notify = require('gulp-notify');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var sassLint = require('gulp-sass-lint');
var uglify = require('gulp-uglify');

var browserSync = require('browser-sync').create();

var paths = {
    appFiles: [
        'app/config/*.yml',
        'app/Resources/**/*.{md,php,twig,yml}'
    ],
    proxy: 'localhost:8000',
    serveDir: '/',

    scriptSrc: 'app/Resources/js/**/*.js',
    scriptDest: 'web/js',

    styleSrc: 'app/Resources/scss/**/*.scss',
    styleDest: 'web/css',

    fontsSrc: [
        'node_modules/font-awesome/fonts/**/*.{ttf,woff,woff2}',
        'node_modules/open-sans-fontface/fonts/Bold/**/*.{ttf,woff,woff2}',
        'node_modules/open-sans-fontface/fonts/BoldItalic/**/*.{ttf,woff,woff2}',
        'node_modules/open-sans-fontface/fonts/Italic/**/*.{ttf,woff,woff2}',
        'node_modules/open-sans-fontface/fonts/Regular/**/*.{ttf,woff,woff2}',
    ],
    fontsDest: 'web/fonts',

    vendorScriptSrc: [
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/jquery-lazyload/jquery.lazyload.js',
        'node_modules/jquery-mousewheel/jquery.mousewheel.js',
        'node_modules/flot/jquery.flot.js',
        'node_modules/flot/jquery.flot.selection.js',
        'node_modules/leaflet/dist/leaflet.js',
        'node_modules/leaflet-iiif/leaflet-iiif.js',
        'node_modules/select2/dist/js/select2.min.js',
    ],
    vendorScriptDest: 'web/js',
};

gulp.task('assets', function () {
    gulp.src(paths.fontsSrc)
        .pipe(gulp.dest(paths.fontsDest));

    gulp.src(paths.vendorScriptSrc)
        .pipe(concat('vendor.js'))
        .pipe(uglify())
        .pipe(gulp.dest(paths.vendorScriptDest));
});

gulp.task('scripts', function () {
    gulp.src(paths.scriptSrc)
        .pipe(newer(paths.scriptDest + '/*.js'))
        .pipe(filter(paths.scriptSrc))
        .pipe(concat('script.js'))
        .pipe(uglify())
        .on('error', notify.onError({title: 'Uglify Error', message: '<%=error%>'}))
        .pipe(gulp.dest(paths.scriptDest))
        .pipe(browserSync.stream());
});

gulp.task('styles', function () {
    gulp.src(paths.styleSrc)
        .pipe(sassLint())
        .pipe(newer(paths.styleDest + '/*.css'))
        .pipe(filter(paths.styleSrc))
        .pipe(sassGlob())
        .pipe(sass())
        .on('error', notify.onError({title: 'Sass Error', message: '<%=error%>'}))
        .pipe(cssnano())
        .pipe(autoprefixer())
        .pipe(gulp.dest(paths.styleDest))
        .pipe(browserSync.stream());
});

gulp.task('compile', ['assets', 'scripts', 'styles']);

gulp.task('default', ['assets', 'scripts', 'styles'], function () {
    browserSync.init({open: false, proxy: paths.proxy});
    gulp.watch(paths.appFiles).on('change', browserSync.reload);
    gulp.watch(paths.assetsSrc, ['assets']);
    gulp.watch(paths.scriptSrc, ['scripts']);
    gulp.watch(paths.styleSrc, ['styles']);
});
