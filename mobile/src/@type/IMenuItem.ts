import { ReactNode } from "react";

export interface IMenuItem {
  type: "IMenuItem";
  icon?: ReactNode;
  text: string;
  onClick?: (item: IMenuItem) => void;
  selected?: boolean;
  dataCy?: string;
}
