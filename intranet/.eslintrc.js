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
    "import/no-extraneous-dependencies": "off",
    "react/jsx-filename-extension": ["error", { extensions: [".jsx", ".tsx"] }],
    "jsx-a11y/control-has-associated-label": "off",
    "jsx-a11y/label-has-associated-control": "off",
    "eslintjsx-a11y/control-has-associated-label": "off",
  },
  globals: {
    $: "readonly",
  },
  overrides: [
    {
      files: ["*.spec.js", "*.spec.ts"],
      rules: {
        "no-unused-expressions": "off",
      },
    },
    {
      files: ["assets/__tests__/**/*.test.ts"],
      rules: {
        "@typescript-eslint/no-empty-function": "off",
        "class-methods-use-this": "off",
        "max-classes-per-file": "off",
      },
    },
    {
      files: ["assets/controllers/**/*.ts"],
      rules: {
        "class-methods-use-this": "off",
        "lines-between-class-members": "off",
      },
    },
  ],
};
