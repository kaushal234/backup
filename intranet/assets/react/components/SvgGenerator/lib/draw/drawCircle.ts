import { G } from "@svgdotjs/svg.js";
import { IDrawConfig } from "../types/IDrawParams";
import { DRAW_COLORS } from "../constants/constants";

export interface IDrawCircleParams {
  draw: G;
  x: number;
  y: number;
  radius: number;
  thickness?: number;
  id?: string;
  config?: IDrawConfig;
}

export const drawCircle = (params: IDrawCircleParams) => {
  const { draw, x, y, radius, thickness = 0.1, config, id } = params;
  const group = draw.group().addClass("circle");

  const circle = group
    .circle(radius * 2)
    .center(x, y)
    .fill("none")
    .stroke({ color: "#000", width: thickness });

  if (id && config?.focusElements?.includes(id)) {
    circle.stroke({ color: DRAW_COLORS.focus });
  }

  return group;
};
