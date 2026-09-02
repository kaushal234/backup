import { IDrawImageParams } from "../draw/drawImage";

export interface IImageElement extends Omit<IDrawImageParams, "draw"> {
  type: "Image";
}
