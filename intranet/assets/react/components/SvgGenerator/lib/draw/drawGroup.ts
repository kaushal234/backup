import { G } from "@svgdotjs/svg.js";
import { drawElement } from "./drawElement";
import { IElement } from "../types/IElement";
import { IDrawConfig } from "../types/IDrawParams";

export interface IDrawGroupParams {
  draw: G;
  name: string;
  elements: Array<IElement>;
  x: number;
  y: number;
  config?: IDrawConfig;
}

export const drawGroup = (params: IDrawGroupParams) => {
  const { draw, x, y, name, elements, config } = params;

  const group = draw.group().addClass(name).translate(x, y);

  elements.forEach((current) =>
    drawElement({ draw: group, element: current, config })
  );
};
