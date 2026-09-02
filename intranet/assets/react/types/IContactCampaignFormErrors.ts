import { IContactCampaignFormData } from "./IContactCampaignFormData";

export type IContactCampaignFormErrors = {
  [K in keyof IContactCampaignFormData]?: string;
};
