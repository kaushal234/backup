import { IDrawRectangleParams } from "../draw/drawRectangle";

export interface IRectangleElement extends Omit<IDrawRectangleParams, "draw"> {
  type: "Rectangle";
}
