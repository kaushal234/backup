import { ReactNode } from "react";

export interface IDrawerMenuItem {
  title: string;
  icon?: ReactNode;
  path: string;
  dataCy?: string;
}
