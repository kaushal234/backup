import { IDrawTextInRectangleParams } from "../draw/drawTextInRectangle";

export interface ITextInRectangleElement
  extends Omit<IDrawTextInRectangleParams, "draw"> {
  type: "TextInRectangle";
}
