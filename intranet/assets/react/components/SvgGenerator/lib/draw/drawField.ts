import { G } from "@svgdotjs/svg.js";
import { drawRectangle } from "./drawRectangle";
import { drawText } from "./drawText";
import { centerTextVertically } from "../utils/centerTextVertically";
import { drawTextInRectangle } from "./drawTextInRectangle";
import { IDrawConfig } from "../types/IDrawParams";

export interface IDrawFieldParams {
  draw: G;
  field: IField;
  leftContent?: IText;
  rightContent?: IText;
  middleContent?: IText;
  id?: string;
  config?: IDrawConfig;
}

interface IField {
  x: number;
  y: number;
  width: number;
  height: number;
  radius?: number;
  thickness?: number;
  gap?: number;
}

interface IText {
  text?: string;
  gap?: number;
  fontSize?: number;
  adjustY?: number;
}

export const drawField = (params: IDrawFieldParams) => {
  const { draw, field, leftContent, rightContent, middleContent, config, id } =
    params;
  const { radius = 1, gap: fieldGap = 0.3 } = field;
  const defaultTextGap = 1;
  const defaultFontSize = 2.85;
  const defaultAdjustY = 0.3;

  const group = draw.group().addClass("field");

  const outerRect = drawRectangle({
    draw: group,
    width: field.width,
    height: field.height,
    x: field.x,
    y: field.y,
    radius,
    thickness: field.thickness,
    config,
    id,
  });

  drawRectangle({
    draw: group,
    width: field.width - fieldGap * 2,
    height: field.height - fieldGap * 2,
    x: field.x + fieldGap,
    y: field.y + fieldGap,
    radius: 0.8,
    thickness: field.thickness,
    config,
    id,
  });

  if (leftContent?.text) {
    const leftGroup = drawText({
      draw: group,
      text: leftContent?.text,
      x: field.x - (leftContent?.gap || defaultTextGap),
      y: field.y,
      isRTL: true,
      fontSize: leftContent?.fontSize || defaultFontSize,
      fontWeight: "bold",
      config,
      id,
    });
    centerTextVertically({
      group: outerRect,
      text: leftGroup,
      adjustY: leftContent?.adjustY || defaultAdjustY,
    });
  }

  if (rightContent?.text) {
    const rightGroup = drawText({
      draw: group,
      text: rightContent.text,
      x: field.x + field.width + (rightContent.gap || defaultTextGap),
      y: field.y,
      fontSize: rightContent.fontSize || defaultFontSize,
      fontWeight: "bold",
      config,
      id,
    });
    centerTextVertically({
      group: outerRect,
      text: rightGroup,
      adjustY: rightContent?.adjustY || defaultAdjustY,
    });
  }

  if (middleContent?.text) {
    drawTextInRectangle({
      draw: group,
      width: field.width - fieldGap * 2,
      height: field.height - fieldGap * 2,
      x: field.x + fieldGap,
      y: field.y + fieldGap,
      text: middleContent.text,
      fontSize: middleContent.fontSize || defaultFontSize,
      alignHorizontally: true,
      alignVertically: true,
      fontWeight: "bold",
      config,
      id,
      adjustY: middleContent?.adjustY || defaultAdjustY,
    });
  }
};
