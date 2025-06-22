const { defineConfig } = require('@vue/cli-service')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    host: 'localhost'
    // Port will be automatically assigned to next available port
  }
})
