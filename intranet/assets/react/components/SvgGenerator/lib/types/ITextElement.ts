import { IDrawTextParams } from "../draw/drawText";

export interface ITextElement extends Omit<IDrawTextParams, "draw"> {
  type: "Text";
}
