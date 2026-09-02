import { IDrawCircleParams } from "../draw/drawCircle";

export interface ICircleElement extends Omit<IDrawCircleParams, "draw"> {
  type: "Circle";
}
