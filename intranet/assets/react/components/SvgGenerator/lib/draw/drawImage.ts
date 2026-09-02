import { G } from "@svgdotjs/svg.js";
import { drawRectangle } from "./drawRectangle";

export interface IDrawImageParams {
  draw: G;
  x: number;
  y: number;
  height: number;
  width: number;
  base64DataUrl?: string;
  id?: string;
}

export const drawImage = (params: IDrawImageParams) => {
  const { draw, x, y, height, width, base64DataUrl } = params;

  const group = draw.group().addClass("image");

  if (base64DataUrl) {
    const image = group.image(base64DataUrl).size(width, height).move(x, y);
    image.attr({ preserveAspectRatio: "none" });
  } else {
    drawRectangle({ draw: group, x, y, width, height, dashed: true });
  }

  return group;
};
