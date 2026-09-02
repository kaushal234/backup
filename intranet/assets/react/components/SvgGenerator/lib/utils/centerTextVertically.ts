import { G } from "@svgdotjs/svg.js";

interface ICenterTextVerticallyParams {
  text: G;
  group: G;
  adjustY?: number;
}

export const centerTextVertically = (params: ICenterTextVerticallyParams) => {
  const { group, text, adjustY = 0 } = params;
  const textHeight = text.bbox().height;
  const groupCenterY = group.bbox().y + group.bbox().height / 2;
  const centeredY = groupCenterY - textHeight / 2;
  text.move(text.bbox().x, centeredY + adjustY);
};
