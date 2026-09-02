import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { toastFailure, toastSuccess } from "../../utils/utils";
import ContactCampaignForm from "../../components/ContactCampaignForm/ContactCampaignForm";
import { IContactCampaignFormData } from "../../types/IContactCampaignFormData";
import { IContactCampaign } from "../../types/IGetContactCampaignResponse";
import {
  IPutContactCampaignApiPayload,
  PutContactCampaign,
} from "../../api/putContactCampaign";
import { getContactCampaignById } from "../../api/getContactContractById";
import { CONTACT_CAMPAIGN_STATUS_OPTIONS } from "../../constants/constants";
import { createBusinessUnitDropdownItem } from "../../utils/dropdown/businessUnit";

function EditContactCampaign() {
  const { id } = useParams();
  const [data, setData] = useState<IContactCampaign | null>(null);

  const handleSubmit = async (values: IContactCampaignFormData) => {
    const params: IPutContactCampaignApiPayload = {
      id: id ?? "",
      name: values.name ?? "",
      description: values.description ?? "",
      startedAt: values.startedAt?.toISOString() ?? "",
      endedAt: values.endedAt?.toISOString() ?? "",
      status: values.status?.value ?? "",
      businessUnits: values.businessUnits?.map((item) => item.value),
      contacts: values.contacts,
    };
    const response = await PutContactCampaign(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/contact-campaigns/${response.data?.id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    if (id) {
      const response = await getContactCampaignById({ id });
      if (response.status === 200 && response.data) {
        setData(response.data);
      }
    }
  };

  useEffect(() => {
    fetchData();
  }, [id]);

  if (!data) return null;

  return (
    <ContactCampaignForm
      isEdit
      onSubmit={handleSubmit}
      initialValues={{
        name: data.name,
        startedAt: new Date(data.startedAt),
        endedAt: new Date(data.endedAt),
        status: CONTACT_CAMPAIGN_STATUS_OPTIONS.find(
          (item) => item.value === data.status
        ),
        businessUnits: data.businessUnits.map((item) =>
          createBusinessUnitDropdownItem(item)
        ),
        description: data.description,
        contacts: data.contacts?.map((contact) => contact["@id"]) || [],
      }}
    />
  );
}

export default EditContactCampaign;
