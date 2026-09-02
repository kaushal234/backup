import React, { useEffect, useState } from "react";
import ContractForm from "../../components/ContractForm/ContractForm";
import { IContractFormData } from "../../types/IContractFormData";
import { IPostContractApiPayload, postContract } from "../../api/postContract";
import {
  filterStringArray,
  toastFailure,
  toastSuccess,
} from "../../utils/utils";
import { useAppDispatch } from "../../hooks/hooks";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";
import { postContractFile } from "../../api/postContractFile";
import { IContractAiFile } from "../../types/IContractAiFile";
import {
  convertContractAiFileToFile,
  mapAiDataToContractFormData,
} from "../../utils/contract";
import { IContractAiData } from "../../types/IContractAiData";

interface IProps {
  aiData?: IContractAiData;
  aiFile?: IContractAiFile;
}

function AddContract(props: IProps) {
  const { aiData, aiFile } = props;
  const dispatch = useAppDispatch();

  const [formData, setFormData] = useState<IContractFormData | null>(null);

  const handleSubmit = async (values: IContractFormData) => {
    const params: IPostContractApiPayload = {
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
      observationValue: values.observationValue,
      externalParty: values.externalParty,
      internalParty: filterStringArray(values.internalParty),
      confidential: values.confidential,
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
      comment: values.comment,
    };
    if (values.indefinitePeriodType) {
      params.expirationDate = null;
      params.renewalPeriod = null;
      params.renewalUnit = null;
    }
    dispatch(showGlobalLoader(true));
    const response = await postContract(params);
    if (response.data && aiFile) {
      await postContractFile({
        id: response.data.id,
        file: convertContractAiFileToFile(aiFile),
      });
    }
    dispatch(showGlobalLoader(false));
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/legal/contracts/${response.data?.id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchFormData = async () => {
    dispatch(showGlobalLoader(true));
    setFormData(await mapAiDataToContractFormData(aiData));
    dispatch(showGlobalLoader(false));
  };

  useEffect(() => {
    fetchFormData();
  }, []);

  if (!formData) return <FullScreenLoader />;

  return <ContractForm onSubmit={handleSubmit} initialValues={formData} />;
}

export default AddContract;
