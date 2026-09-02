import { G } from "@svgdotjs/svg.js";
import { IDrawConfig } from "../types/IDrawParams";
import { DRAW_COLORS } from "../constants/constants";

export interface IDrawLineParams {
  draw: G;
  startX: number;
  startY: number;
  endX: number;
  endY: number;
  thickness?: number;
  id?: string;
  config?: IDrawConfig;
}

export const drawLine = (params: IDrawLineParams) => {
  const {
    draw,
    startX,
    startY,
    endX,
    endY,
    thickness = 0.1,
    config,
    id,
  } = params;

  const group = draw.group().addClass("line");

  group
    .line(startX, startY, endX, endY)
    .stroke({ color: "#000", width: thickness })
    .attr({ "vector-effect": "non-scaling-stroke" });

  if (id && config?.focusElements?.includes(id)) {
    group.stroke({ color: DRAW_COLORS.focus });
  }
};
