module.exports = {
  extends: "airbnb-typescript-prettier",
  rules: {
    "import/no-cycle": "off",
    "import/prefer-default-export": "off",
    "no-console": ["warn", { allow: ["error", "info"] }],
    "no-plusplus": "off",
    "import/extensions": [
      "error",
      "ignorePackages",
      {
        ts: "always",
        js: "always",
      },
    ],
  },
};
