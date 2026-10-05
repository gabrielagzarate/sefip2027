let mix = require('laravel-mix');

mix.options({
  loaders: [
    { loader: 'expose?$!expose?jQuery' }
  ]
})
  .js('.src/bootstrap.min.js', 'js/lib')
  .js('.src/alertify.min.js', 'assets/js/')
  .js('.src/libreriasSefip.js', 'js/libreriasSefip.min.js')
  .js('.src/codemirror.js', 'js/codemirror.min.js')
  .js('.src/jquery-treeview.js', 'js/jquery.treeview.js')
  .js('.src/jquery-migrate.js','js/jquery-migrate.min.js')

mix
  .js('.src/ladda.js','js/ladda.min.js')
  .js('.src/spin.js','js/spin.min.js')
  .js('.src/axios.js','js/axios.min.js')
  .sass('.src/alertify.scss', 'assets/css/alertify.min.css')
  .sass('.src/codemirror.scss','css/codemirror.css')
  .sourceMaps(true, 'source-map');