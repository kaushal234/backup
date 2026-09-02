import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import ContractForm from "../../components/ContractForm/ContractForm";
import { IContractFormData } from "../../types/IContractFormData";
import { IContract } from "../../types/IGetContractByIdResponse";
import { getContractById } from "../../api/getContractById";
import { createContractSubCategoryDropdownItem } from "../../utils/dropdown/contractSubCategory";
import { createContractCategoryDropdownItem } from "../../utils/dropdown/contractCategory";
import { createContractDropdownItem } from "../../utils/dropdown/contract";
import { createDivisionDropdownItem } from "../../utils/dropdown/division";
import { createRegionDropdownItem } from "../../utils/dropdown/region";
import { createBusinessUnitDropdownItem } from "../../utils/dropdown/businessUnit";
import { createPremiseDropdownItem } from "../../utils/dropdown/premise";
import { createCurrencyDropdownItem } from "../../utils/dropdown/currency";
import { CONTRACT_RENEWAL_UNIT_OPTIONS } from "../../constants/constants";
import { IPutContractApiPayload, putContract } from "../../api/putContract";
import {
  filterStringArray,
  toastFailure,
  toastSuccess,
} from "../../utils/utils";
import { createPeopleDropdownItem } from "../../utils/dropdown/people";
import { createCustomerDropdownItem } from "../../utils/dropdown/customer";

function EditContract() {
  const { id } = useParams();
  const [data, setData] = useState<IContract | null>(null);

  const handleSubmit = async (values: IContractFormData) => {
    const params: IPutContractApiPayload = {
      id: id ?? "",
      data: {
        shortDescription: values.shortDescription ?? "",
        description: values.description ?? "",
        startDate: values.startDate?.toISOString() ?? "",
        expirationDate: values.expirationDate
          ? values.expirationDate?.toISOString()
          : null,
        indefinitePeriodType: values.indefinitePeriodType,
        renewalPeriod: values.renewalPeriod ? +values.renewalPeriod : null,
        renewalUnit: values.renewalUnit?.value,
        automaticRenewal: values.automaticRenewal,
        observationTerm: values.observationTerm,
        confidential: values.confidential,
        observationValue: values.observationValue,
        externalParty: values.externalParty,
        internalParty: filterStringArray(values.internalParty),
        otherPartySignatories: filterStringArray(values.otherPartySignatories),
        jurisdiction: values.jurisdiction,
        value: values.value ? +values.value : null,
        currency: values.currency?.value,
        parentContract: values.parentContract?.value,
        subCategory: values.subCategory?.value ?? "",
        divisions: values.divisions?.map((item) => item.value),
        regions: values.regions?.map((item) => item.value),
        premises: values.premises?.map((item) => item.value),
        businessUnits: values.businessUnits?.map((item) => item.value),
        customers: values.customers?.map((item) => item.value),
        owner: values.owner?.value,
        comment: values.comment,
      },
    };
    if (values.indefinitePeriodType) {
      params.data.expirationDate = null;
      params.data.renewalPeriod = null;
      params.data.renewalUnit = null;
    }
    const response = await putContract(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/legal/contracts/${response.data?.id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    if (id) {
      const response = await getContractById({ id });
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
    <ContractForm
      isEdit
      onSubmit={handleSubmit}
      initialValues={{
        shortDescription: data.shortDescription,
        category: createContractCategoryDropdownItem(data.subCategory.category),
        subCategory: createContractSubCategoryDropdownItem(data.subCategory),
        description: data.description,
        parentContract: data.parentContract
          ? createContractDropdownItem(data.parentContract)
          : undefined,
        jurisdiction: data.jurisdiction || "",
        startDate: new Date(data.startDate),
        divisions: data.divisions.map((item) =>
          createDivisionDropdownItem(item)
        ),
        regions: data.regions.map((item) => createRegionDropdownItem(item)),
        businessUnits: data.businessUnits.map((item) =>
          createBusinessUnitDropdownItem(item)
        ),
        customers: data.customers.map((item) =>
          createCustomerDropdownItem(item)
        ),
        premises: data.premises.map((item) => createPremiseDropdownItem(item)),
        value: `${data.value || ""}`,
        currency: data.currency
          ? createCurrencyDropdownItem(data.currency)
          : undefined,
        expirationDate: data.expirationDate
          ? new Date(data.expirationDate)
          : null,
        renewalPeriod: `${data.renewalPeriod || ""}`,
        renewalUnit: CONTRACT_RENEWAL_UNIT_OPTIONS.find(
          (item) => item.value === data.renewalUnit
        ),
        indefinitePeriodType: data.indefinitePeriodType,
        confidential: data.confidential,
        automaticRenewal: data.automaticRenewal,
        observationTerm: data.observationTerm || "",
        observationValue: data.observationValue || "",
        externalParty: data.externalParty || "",
        internalParty: data.internalParty,
        otherPartySignatories: data.otherPartySignatories,
        owner: createPeopleDropdownItem(data.owner),
        comment: data.comment || "",
      }}
    />
  );
}

export default EditContract;
