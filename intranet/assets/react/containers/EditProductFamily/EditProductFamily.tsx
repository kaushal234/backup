import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { getProductFamilyById } from "../../api/getProductFamilyById";
import {
  IPutProductFamilyApiPayload,
  putProductFamily,
} from "../../api/putProductFamily";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { IProductFamily } from "../../types/IGetProductFamilyByIdResponse";
import { IProductFamilyFormData } from "../../types/IProductFamilyFormData";
import ProductFamilyForm from "../../components/ProductFamilyForm/ProductFamilyForm";
import { createProductTypeDropdownItem } from "../../utils/dropdown/productType";
import { createProductFamilyTagDropdownItem } from "../../utils/dropdown/productFamilyTag";
import { createManufacturingFactoryDropdownItem } from "../../utils/dropdown/manufacturingFactory";

function EditProductFamily() {
  const { id } = useParams();
  const [data, setData] = useState<IProductFamily | null>(null);

  const handleSubmit = async (values: IProductFamilyFormData) => {
    const params: IPutProductFamilyApiPayload = {
      id: id ?? "",
      data: {
        name: values.name,
        productType: values.productType?.value ?? "",
        tags: values.tags?.map((item) => item.value),
        manufacturingFactories: values.manufacturingFactories?.map(
          (item) => item.value
        ),
        publicForTLD: values.publicForTLD,
        publicForAerospecialties: values.publicForAerospecialties,
        publicForSAS: values.publicForSAS,
        hidden: values.hidden,
        englishDescription: values.englishDescription ?? null,
        frenchDescription: values.frenchDescription ?? null,
        spanishDescription: values.spanishDescription ?? null,
        portugueseDescription: values.portugueseDescription ?? null,
        chineseDescription: values.chineseDescription ?? null,
        japaneseDescription: values.japaneseDescription ?? null,
        germanDescription: values.germanDescription ?? null,
        russianDescription: values.russianDescription ?? null,
      },
    };

    const response = await putProductFamily(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/catalogue/families/${response.data?.id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    if (id) {
      const response = await getProductFamilyById({ id });
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
    <ProductFamilyForm
      isEdit
      onSubmit={handleSubmit}
      initialValues={{
        name: data.name,
        productType: createProductTypeDropdownItem(data.productType),
        tags: data.tags.map((item) => createProductFamilyTagDropdownItem(item)),
        manufacturingFactories: data.manufacturingFactories.map((item) =>
          createManufacturingFactoryDropdownItem(item)
        ),
        publicForTLD: data.publicForTLD,
        publicForAerospecialties: data.publicForAerospecialties,
        publicForSAS: data.publicForSAS,
        hidden: data.hidden,
        englishDescription: data.englishDescription || "",
        frenchDescription: data.frenchDescription || "",
        spanishDescription: data.spanishDescription || "",
        portugueseDescription: data.portugueseDescription || "",
        chineseDescription: data.chineseDescription || "",
        japaneseDescription: data.japaneseDescription || "",
        germanDescription: data.germanDescription || "",
        russianDescription: data.russianDescription || "",
      }}
    />
  );
}

export default EditProductFamily;
