import { ICELogoElement } from "./ICELogoElement";
import { ICircleElement } from "./ICircleElement";
import { IFieldElement } from "./IFieldElement";
import { IGroupElement } from "./IGroupElement";
import { IImageElement } from "./IImageElement";
import { ILineElement } from "./ILineElement";
import { IRectangleElement } from "./IRectangleElement";
import { ITextElement } from "./ITextElement";
import { ITextInRectangleElement } from "./ITextInRectangleElement";

export type IElement =
  | IGroupElement
  | IRectangleElement
  | ILineElement
  | IFieldElement
  | ITextElement
  | ICircleElement
  | ICELogoElement
  | IImageElement
  | ITextInRectangleElement;
