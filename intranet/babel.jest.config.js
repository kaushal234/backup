const path = require("path");

module.exports = {
  presets: [
    [
      "@babel/preset-env",
      {
        targets: { node: "current" },
        useBuiltIns: "usage",
        corejs: "3",
      },
    ],
    "@babel/preset-react",
  ],
  plugins: [
    ["@babel/plugin-transform-typescript", { allowDeclareFields: true }],
    "@babel/plugin-proposal-export-default-from",
    "@babel/plugin-proposal-class-properties",
  ],
  include: [path.resolve(__dirname, "assets")],
};
