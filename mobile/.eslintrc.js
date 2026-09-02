module.exports = {
  extends: "airbnb-typescript-prettier",
  rules: {
    "react/require-default-props": "off",
    "react-hooks/exhaustive-deps": "off",
    "react/jsx-props-no-spreading": "off",
    "import/no-cycle": "off",
    "import/prefer-default-export": "off",
    "no-console": ["warn", { allow: ["error", "info"] }],
    "no-plusplus": "off",
    "jsx-a11y/media-has-caption": "off",
    "jsx-a11y/anchor-is-valid": "off",
    "no-useless-escape": "off",
  },
};
