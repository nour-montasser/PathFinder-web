const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Output directories
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Main JS & CSS files
    .addEntry('app', './assets/app.js')

   

    // Split vendor chunks (useful for performance)
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    // Clean up before build
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Enable SCSS
    .enableSassLoader()

    // Copy images and fonts to public build
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })
    .copyFiles({
        from: './assets/vendor', // If you have fonts here
        to: 'vendor/[path][name].[ext]',
    });

module.exports = Encore.getWebpackConfig();