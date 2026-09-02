import { SVG } from "@svgdotjs/svg.js";
import { drawElement } from "./drawElement";
import { drawViewBox } from "./drawViewBox";
import { IElement } from "../types/IElement";
import { IDrawConfig } from "../types/IDrawParams";
import { defineFonts } from "../utils/defineFonts";

interface IDrawSvgParams {
  elementRef: HTMLDivElement;
  padding?: number;
  drawing: IElement;
  config?: IDrawConfig;
}

export const drawSvg = (params: IDrawSvgParams) => {
  const { elementRef, padding = 10, drawing, config } = params;
  const draw = SVG().addTo(elementRef);

  defineFonts({ draw });

  const group = draw.group().addClass("main");

  drawElement({ draw: group, element: drawing, config });

  drawViewBox({ draw, padding });

  return draw;
};
