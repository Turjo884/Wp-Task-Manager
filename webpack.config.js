const path = require('path');


module.exports =()=>{
   return{
      entry: {
         'admin': path.resolve(__dirname,'src/admin/Admin.jsx'),

      },
      output:{
         filename: 'admin.js',
         path: path.resolve(__dirname,'src/build')
      },
      module:{
         rules:[
            {
               test: /\.jsx?$/,
               use:{
                  loader: 'babel-loader',
                  options:{
                     presets: ['@babel/preset-react']
                  }
               }
            }
         ]
      }
   }
}