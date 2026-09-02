import { IDropdownItem } from "./IDropdownItem";

export interface IContactCampaignFormData {
  name?: string;
  startedAt?: Date | null;
  endedAt?: Date | null;
  status?: IDropdownItem | null;
  description?: string;
  businessUnits?: Array<IDropdownItem>;
  contacts?: Array<string>;
  customerFilter?: Array<IDropdownItem>;
  locationFilter?: Array<IDropdownItem>;
}
