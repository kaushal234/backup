import { IDrawGroupParams } from "../draw/drawGroup";

export interface IGroupElement extends Omit<IDrawGroupParams, "draw"> {
  type: "Group";
}
