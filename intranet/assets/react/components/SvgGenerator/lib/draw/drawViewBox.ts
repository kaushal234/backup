import { Svg } from "@svgdotjs/svg.js";

interface IDrawViewBoxParams {
  draw: Svg;
  padding: number;
}

export const drawViewBox = (params: IDrawViewBoxParams) => {
  const { draw, padding } = params;

  const contentBox = draw.bbox();

  const viewBoxX = contentBox.x - padding;
  const viewBoxY = contentBox.y - padding;
  const viewBoxWidth = contentBox.width + padding * 2;
  const viewBoxHeight = contentBox.height + padding * 2;

  draw.viewbox(viewBoxX, viewBoxY, viewBoxWidth, viewBoxHeight);

  draw.attr({ preserveAspectRatio: "xMidYMid meet" });

  const widthCm = viewBoxWidth / 10;
  const heightCm = viewBoxHeight / 10;
  draw.size(`${widthCm}cm`, `${heightCm}cm`);
};
