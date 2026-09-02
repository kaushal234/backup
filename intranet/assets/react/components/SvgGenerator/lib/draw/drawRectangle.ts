import { G } from "@svgdotjs/svg.js";
import { IDrawConfig } from "../types/IDrawParams";
import { DRAW_COLORS } from "../constants/constants";

export interface IDrawRectangleParams {
  draw: G;
  x: number;
  y: number;
  width: number;
  height: number;
  radius?: number;
  thickness?: number;
  dashed?: boolean;
  id?: string;
  config?: IDrawConfig;
}

export const drawRectangle = (params: IDrawRectangleParams) => {
  const {
    draw,
    x,
    y,
    width,
    height,
    radius,
    thickness = 0.1,
    dashed,
    config,
    id,
  } = params;
  const group = draw.group().addClass("rect");

  const rect = group
    .rect(width, height)
    .move(x, y)
    .fill("none")
    .stroke({
      color: "#000",
      width: thickness,
      ...(dashed && { dasharray: "1,1" }),
    })
    .attr({ "vector-effect": "non-scaling-stroke" });

  if (radius) {
    rect.radius(radius);
  }

  if (id && config?.focusElements?.includes(id)) {
    rect.stroke({ color: DRAW_COLORS.focus });
  }

  return group;
};
