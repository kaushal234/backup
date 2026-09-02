import { G } from "@svgdotjs/svg.js";
import { drawRectangle } from "./drawRectangle";
import { drawText } from "./drawText";
import { IDrawConfig } from "../types/IDrawParams";

export interface IDrawTextInRectangleParams {
  draw: G;
  text: string;
  fontSize?: number;
  x: number;
  y: number;
  width: number;
  height: number;
  dashed?: boolean;
  alignVertically?: boolean;
  alignHorizontally?: boolean;
  textCenter?: boolean;
  id?: string;
  fontWeight?: "bold" | "normal";
  config?: IDrawConfig;
  adjustY?: number;
}

export const drawTextInRectangle = (params: IDrawTextInRectangleParams) => {
  const {
    draw,
    text,
    fontSize = 4,
    x,
    y,
    width,
    height,
    dashed,
    alignVertically,
    alignHorizontally,
    textCenter,
    fontWeight,
    config,
    id,
    adjustY = 0,
  } = params;

  const group = draw.group().addClass("text-rectangle");

  const rectangleElement = drawRectangle({
    draw: group,
    x,
    y,
    height,
    width,
    dashed,
    config,
    id,
  });

  const textElement = drawText({
    draw: group,
    x,
    y,
    fontSize,
    text,
    textCenter,
    fontWeight,
    config,
    id,
  });

  let textX = rectangleElement.bbox().x;
  let textY = rectangleElement.bbox().y;

  if (alignHorizontally) {
    textX =
      rectangleElement.bbox().x +
      rectangleElement.bbox().width / 2 -
      textElement.bbox().width / 2;
  }

  if (alignVertically) {
    textY =
      rectangleElement.bbox().y +
      rectangleElement.bbox().height / 2 -
      textElement.bbox().height / 2;
  }

  textElement.move(textX, textY + adjustY);

  if (!dashed) {
    rectangleElement.remove();
  }

  return group;
};
