import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { IContractCategoryFormData } from "../../types/IContractCategoryFormData";
import { ICategory } from "../../types/IGetContractCategoryByIdResponse";
import {
  IPutContractCategoryApiPayload,
  putContractCategory,
} from "../../api/putContractCategory";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { getContractCategoryById } from "../../api/getContractCategoryById";
import ContractCategoryForm from "../../components/ContractCategoryForm/ContractCategoryForm";

function EditContractCategory() {
  const { id } = useParams();
  const [data, setData] = useState<ICategory | null>(null);

  const handleSubmit = async (values: IContractCategoryFormData) => {
    const params: IPutContractCategoryApiPayload = {
      id: id ?? "",
      data: {
        displayedName: values.displayedName ?? "",
      },
    };
    const response = await putContractCategory(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/legal/contract/categories`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    if (id) {
      const response = await getContractCategoryById({ id });
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
    <ContractCategoryForm
      initialValues={{
        displayedName: data.displayedName,
      }}
      onSubmit={handleSubmit}
    />
  );
}

export default EditContractCategory;
