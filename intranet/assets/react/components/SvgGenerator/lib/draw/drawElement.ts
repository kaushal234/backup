import { G } from "@svgdotjs/svg.js";
import { IElement } from "../types/IElement";
import { drawRectangle } from "./drawRectangle";
import { drawLine } from "./drawLine";
import { drawField } from "./drawField";
import { drawText } from "./drawText";
import { drawCELogo } from "./drawCELogo";
import { drawCircle } from "./drawCircle";
import { drawImage } from "./drawImage";
import { drawTextInRectangle } from "./drawTextInRectangle";
import { drawGroup } from "./drawGroup";
import { IDrawConfig } from "../types/IDrawParams";

interface IDrawElementParams {
  draw: G;
  element: IElement;
  config?: IDrawConfig;
}

export const drawElement = (params: IDrawElementParams) => {
  const { draw, element, config } = params;

  switch (element.type) {
    case "Group": {
      drawGroup({
        draw,
        x: element.x,
        y: element.y,
        name: element.name,
        elements: element.elements,
        config,
      });
      break;
    }
    case "Rectangle": {
      drawRectangle({
        draw,
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height,
        radius: element.radius,
        thickness: element.thickness,
        dashed: element.dashed,
        id: element.id,
        config,
      });
      break;
    }
    case "Line": {
      drawLine({
        draw,
        startX: element.startX,
        startY: element.startY,
        endX: element.endX,
        endY: element.endY,
        thickness: element.thickness,
        id: element.id,
        config,
      });
      break;
    }
    case "Field": {
      drawField({
        draw,
        field: {
          x: element.field.x,
          y: element.field.y,
          width: element.field.width,
          height: element.field.height,
          radius: element.field.radius,
          thickness: element.field.thickness,
        },
        leftContent: {
          text: element.leftContent?.text,
          gap: element.leftContent?.gap,
          fontSize: element.leftContent?.fontSize,
          adjustY: element.leftContent?.adjustY,
        },
        rightContent: {
          text: element.rightContent?.text,
          gap: element.rightContent?.gap,
          fontSize: element.rightContent?.fontSize,
          adjustY: element.rightContent?.adjustY,
        },
        middleContent: {
          text: element.middleContent?.text,
          gap: element.middleContent?.gap,
          fontSize: element.middleContent?.fontSize,
          adjustY: element.middleContent?.adjustY,
        },
        id: element.id,
        config,
      });
      break;
    }
    case "Text": {
      drawText({
        draw,
        text: element.text,
        x: element.x,
        y: element.y,
        fontSize: element.fontSize,
        isRTL: element.isRTL,
        textCenter: element.textCenter,
        id: element.id,
        config,
      });
      break;
    }
    case "Circle": {
      drawCircle({
        draw,
        x: element.x,
        y: element.y,
        radius: element.radius,
        thickness: element.thickness,
        id: element.id,
        config,
      });
      break;
    }
    case "CE": {
      drawCELogo({
        draw,
        x: element.x,
        y: element.y,
        id: element.id,
        config,
      });
      break;
    }
    case "Image": {
      drawImage({
        draw,
        x: element.x,
        y: element.y,
        height: element.height,
        width: element.width,
        base64DataUrl: element.base64DataUrl,
        id: element.id,
      });
      break;
    }
    case "TextInRectangle": {
      drawTextInRectangle({
        draw,
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height,
        text: element.text,
        fontSize: element.fontSize,
        dashed: element.dashed,
        alignHorizontally: element.alignHorizontally,
        alignVertically: element.alignVertically,
        textCenter: element.textCenter,
        fontWeight: element.fontWeight,
        id: element.id,
        adjustY: element.adjustY,
        config,
      });
      break;
    }
    default: {
      break;
    }
  }
};
