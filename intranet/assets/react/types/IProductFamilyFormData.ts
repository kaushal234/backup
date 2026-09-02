import { IDropdownItem } from "./IDropdownItem";

export interface IProductFamilyFormData {
  id?: string;
  name?: string;
  productType?: IDropdownItem | null;
  tags: Array<IDropdownItem>;
  manufacturingFactories: Array<IDropdownItem>;
  publicForTLD?: boolean;
  publicForAerospecialties?: boolean;
  publicForSAS?: boolean;
  hidden?: boolean;
  englishDescription?: string;
  frenchDescription?: string;
  spanishDescription?: string;
  portugueseDescription?: string;
  chineseDescription?: string;
  japaneseDescription?: string;
  germanDescription?: string;
  russianDescription?: string;
}
