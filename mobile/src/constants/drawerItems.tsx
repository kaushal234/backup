import { IDrawerMenuItem } from "../@type/IDrawerMenuItem";
import { ROUTES } from "./routes";

export const DRAWER_ITEMS: Array<IDrawerMenuItem> = [
  {
    title: "drawer.home",
    path: ROUTES.home,
  },
  {
    title: "drawer.toc.home",
    path: ROUTES.toc.home,
  },
  {
    title: "drawer.csr",
    path: ROUTES.csr.home,
  },
  {
    title: "drawer.er.home",
    path: ROUTES.er.home,
  },
  {
    title: "drawer.er.finder",
    path: ROUTES.er.finder,
  },
  {
    title: "drawer.toc.search",
    path: ROUTES.toc.search,
  },
];
