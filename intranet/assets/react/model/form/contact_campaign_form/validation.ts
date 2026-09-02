import { IContactCampaignFormData } from "../../../types/IContactCampaignFormData";
import { IContactCampaignFormErrors } from "../../../types/IContactCampaignFormErrors";

export const validate = (values: IContactCampaignFormData) => {
  const errors: IContactCampaignFormErrors = {};

  if (!values.name) {
    errors.name = "Required";
  }
  if (!values.startedAt) {
    errors.startedAt = "Required";
  }
  if (!values.endedAt) {
    errors.endedAt = "Required";
  }
  if (!values.description) {
    errors.description = "Required";
  }
  if (!values.status) {
    errors.status = "Required";
  }
  if (!values.businessUnits || values.businessUnits.length === 0) {
    errors.businessUnits = "Required";
  }
  if (!values.contacts || values.contacts.length === 0) {
    errors.contacts = "Required";
  }
  return errors;
};
