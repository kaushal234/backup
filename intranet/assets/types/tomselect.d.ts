import type TomSelect from "tom-select";

export interface TomSelectElement extends HTMLSelectElement {
  tomselect: TomSelect;
}

export type TomOption = { [key: string]: any };
