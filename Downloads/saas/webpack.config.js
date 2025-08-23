// webpack.config.js
const Encore = require('@symfony/webpack-encore');
const RtlCssPlugin = require('rtlcss-webpack-plugin');

Encore
  .setOutputPath('public/assets/')
  .setPublicPath('/assets')
  .addEntry('app', './assets/scss/config/saas/app.scss')
  .addEntry('bootstrap', './assets/scss/config/saas/bootstrap.scss')
  .addEntry('icons', './assets/scss/icons.scss')
  .addEntry('custom', './assets/scss/config/saas/custom.scss')
  .enableSingleRuntimeChunk()
  .enableSassLoader()
  .enablePostCssLoader() // This enables PostCSS loader
  .configureFilenames({
    css: 'css/[name]-rtl.min.css',
  })

  .copyFiles({
    from: './assets/fonts',
    to: 'fonts/[name].[ext]',
  })

  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[ext]',
  })

  .copyFiles({
    from: './assets/js',
    to: 'js/[path][name].[ext]',
  })

  .copyFiles({
    from: './assets/json',
    to: 'json/[name].[ext]',
  })

  .copyFiles({
    from: './assets/lang',
    to: 'lang/[name].[ext]',
  })

  .copyFiles({
    from: './assets/libs',
    to: 'libs/[path][name].[ext]',
  })

  .addPlugin(new RtlCssPlugin({
    filename: 'css/[name].min.css',
  }))

  /*
   * FEATURE CONFIG
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications();

module.exports = Encore.getWebpackConfig();
