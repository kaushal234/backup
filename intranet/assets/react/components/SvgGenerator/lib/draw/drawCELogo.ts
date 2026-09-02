import { G } from "@svgdotjs/svg.js";
import { drawCircle } from "./drawCircle";
import { drawCE } from "./drawCE";
import { IDrawConfig } from "../types/IDrawParams";

export interface IDrawCELogoParams {
  draw: G;
  x: number;
  y: number;
  id?: string;
  config?: IDrawConfig;
  showCircle?: boolean;
}

export const drawCELogo = (params: IDrawCELogoParams) => {
  const { draw, x, y, config, id, showCircle = false } = params;

  const radius = 10;

  const group = draw.group().addClass("ce-logo");

  if (showCircle) {
    drawCircle({ draw: group, x, y, radius, config, id });
  }

  drawCE({
    draw: group,
    x,
    y,
    radius,
    config,
    id,
  });

  return group;
};
