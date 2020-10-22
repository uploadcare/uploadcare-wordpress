const defaults = require("@wordpress/scripts/config/webpack.config");
const overrideConf = Object.assign({}, defaults)

Object.assign(overrideConf.resolve, { extensions: [".ts", ".tsx", ".js"] })
overrideConf.module.rules.push({ test: /\.tsx?|\.jsx$/, loader: "ts-loader" })

module.exports = overrideConf;
