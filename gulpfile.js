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
    appFiles: 'app/Resources/**/*.{md,php,twig,yml}',
    proxy: 'localhost:8000',
    serveDir: '/',

    fontsSrc: 'node_modules/open-sans-fontface/fonts/**',
    fontsDest: 'web/fonts/open-sans',
    scriptSrc: 'app/Resources/js/**/*.js',
    scriptDest: 'web/js',
    styleSrc: 'app/Resources/scss/**/*.scss',
    styleDest: 'web/css'
};

gulp.task('fonts', function() {
    gulp.src(paths.fontsSrc)
        .pipe(gulp.dest(paths.fontsDest));
});

gulp.task('scripts', function() {
    gulp.src(paths.scriptSrc)
        .pipe(newer(paths.scriptDest + '/*.js'))
        .pipe(filter(paths.scriptSrc))
        .pipe(concat('script.js'))
        .pipe(uglify())
        .on('error', notify.onError({title: 'Uglify Error', message: '<%=error%>'}))
        .pipe(gulp.dest(paths.scriptDest))
        .pipe(browserSync.stream());
});

gulp.task('styles', function() {
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

gulp.task('compile', ['fonts', 'scripts', 'styles']);

gulp.task('default', ['scripts', 'styles'], function() {
    browserSync.init({open: false, proxy: paths.proxy});
    gulp.watch(paths.appFiles).on('change', browserSync.reload);
    gulp.watch(paths.fontsSrc, ['fonts']);
    gulp.watch(paths.scriptSrc, ['scripts']);
    gulp.watch(paths.styleSrc, ['styles']);
});
