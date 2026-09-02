import { IDrawFieldParams } from "../draw/drawField";

export interface IFieldElement extends Omit<IDrawFieldParams, "draw"> {
  type: "Field";
}
