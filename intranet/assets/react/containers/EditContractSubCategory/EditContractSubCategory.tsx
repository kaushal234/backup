import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { ISubCategory } from "../../types/IGetContractSubCategoryByIdResponse";
import { IContractSubCategoryFormData } from "../../types/IContractSubCategoryFormData";
import {
  IPutContractSubCategoryApiPayload,
  putContractSubCategory,
} from "../../api/putContractSubCategory";
import { getContractSubCategoryById } from "../../api/getContractSubCategoryById";
import ContractSubCategoryForm from "../../components/ContractSubCategoryForm/ContractSubCategoryForm";

function EditContractSubCategory() {
  const { id } = useParams();
  const [data, setData] = useState<ISubCategory | null>(null);

  const handleSubmit = async (values: IContractSubCategoryFormData) => {
    const params: IPutContractSubCategoryApiPayload = {
      id: id ?? "",
      data: {
        displayedName: values.displayedName ?? "",
      },
    };
    const response = await putContractSubCategory(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/legal/contracts/sub-categories`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    if (id) {
      const response = await getContractSubCategoryById({ id });
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
    <ContractSubCategoryForm
      initialValues={{
        displayedName: data.displayedName,
      }}
      onSubmit={handleSubmit}
    />
  );
}

export default EditContractSubCategory;
