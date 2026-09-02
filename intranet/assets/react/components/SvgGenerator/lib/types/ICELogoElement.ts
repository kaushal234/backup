import { IDrawCELogoParams } from "../draw/drawCELogo";

export interface ICELogoElement extends Omit<IDrawCELogoParams, "draw"> {
  type: "CE";
}
