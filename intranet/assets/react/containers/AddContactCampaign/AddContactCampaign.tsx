import React from "react";
import { toastFailure, toastSuccess } from "../../utils/utils";
import ContactCampaignForm from "../../components/ContactCampaignForm/ContactCampaignForm";
import { IContactCampaignFormData } from "../../types/IContactCampaignFormData";
import {
  IPostContactCampaignApiPayload,
  postContactCampaign,
} from "../../api/postContactCampaign";

function AddContactCampaign() {
  const handleSubmit = async (values: IContactCampaignFormData) => {
    const params: IPostContactCampaignApiPayload = {
      name: values.name ?? "",
      description: values.description ?? "",
      startedAt: values.startedAt?.toISOString() ?? "",
      endedAt: values.endedAt?.toISOString() ?? "",
      status: values.status?.value ?? "",
      businessUnits: values.businessUnits?.map((item) => item.value),
      contacts: values.contacts,
    };
    const response = await postContactCampaign(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/contact-campaigns/${response.data?.id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  return <ContactCampaignForm onSubmit={handleSubmit} />;
}

export default AddContactCampaign;
