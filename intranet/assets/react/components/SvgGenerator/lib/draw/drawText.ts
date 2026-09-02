import { G } from "@svgdotjs/svg.js";
import { IDrawConfig } from "../types/IDrawParams";
import { DRAW_COLORS } from "../constants/constants";

export interface IDrawTextParams {
  draw: G;
  text: string;
  fontSize?: number;
  x: number;
  y: number;
  isRTL?: boolean;
  textCenter?: boolean;
  id?: string;
  fontWeight?: "bold" | "normal";
  config?: IDrawConfig;
}

export const drawText = (params: IDrawTextParams) => {
  const {
    draw,
    text,
    fontSize = 4,
    x,
    y,
    isRTL,
    textCenter,
    fontWeight = "normal",
    config,
    id,
  } = params;

  const group = draw.group().addClass("text");

  const textElement = group
    .text(text)
    .font({
      family: "arial-condensed-bold",
      size: fontSize,
      weight: fontWeight,
    })
    .attr({
      "vector-effect": "non-scaling-stroke",
    })
    .css("white-space", "pre")
    .css("text-align", "center")
    .move(x, y);

  if (textCenter) {
    textElement.attr({ "text-anchor": "middle" });
  }

  if (isRTL) {
    const { width } = group.bbox();
    group.move(x - width, y);
  }

  if (id && config?.focusElements?.includes(id)) {
    textElement.fill(DRAW_COLORS.focus);
  }

  return group;
};
