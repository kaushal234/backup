import { IDrawLineParams } from "../draw/drawLine";

export interface ILineElement extends Omit<IDrawLineParams, "draw"> {
  type: "Line";
}
