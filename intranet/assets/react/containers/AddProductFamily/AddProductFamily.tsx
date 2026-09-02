import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import ProductFamilyForm from "../../components/ProductFamilyForm/ProductFamilyForm";
import { IProductFamilyFormData } from "../../types/IProductFamilyFormData";
import {
  IPostProductFamilyApiPayload,
  postProductFamily,
} from "../../api/postProductFamily";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { getProductTypeById } from "../../api/getProductTypeById";
import { createProductTypeDropdownItem } from "../../utils/dropdown/productType";
import { IProductType } from "../../types/IGetAllProductTypesResponse";

function AddProductFamily() {
  const { id } = useParams<{ id: string }>();
  const [productTypeApiItem, setProductTypeApiItem] =
    useState<IProductType | null>(null);

  const fetchProductType = async () => {
    if (id) {
      const productItemResponse = await getProductTypeById({ id });
      if (productItemResponse.status === 200 && productItemResponse.data) {
        setProductTypeApiItem(productItemResponse.data);
      }
    }
  };

  useEffect(() => {
    fetchProductType();
  }, [id]);

  const handleSubmit = async (values: IProductFamilyFormData) => {
    const params: IPostProductFamilyApiPayload = {
      name: values.name?.trim() ?? "",
      productType: values.productType?.value ?? "",

      tags: (values.tags ?? []).map((tag) => tag.value),
      manufacturingFactories: (values.manufacturingFactories ?? []).map(
        (factory) => factory.value
      ),

      publicForTLD: !!values.publicForTLD,
      publicForAerospecialties: !!values.publicForAerospecialties,
      publicForSAS: !!values.publicForSAS,
      hidden: !!values.hidden,

      englishDescription: values.englishDescription?.trim() || null,
      frenchDescription: values.frenchDescription?.trim() || null,
      spanishDescription: values.spanishDescription?.trim() || null,
      portugueseDescription: values.portugueseDescription?.trim() || null,
      chineseDescription: values.chineseDescription?.trim() || null,
      japaneseDescription: values.japaneseDescription?.trim() || null,
      germanDescription: values.germanDescription?.trim() || null,
      russianDescription: values.russianDescription?.trim() || null,
    };

    const response = await postProductFamily(params);

    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/catalogue/types/${response?.data?.productType?.id}`;
    } else {
      await toastFailure(response.message);
    }
  };

  return (
    <ProductFamilyForm
      onSubmit={handleSubmit}
      initialValues={{
        productType: productTypeApiItem
          ? createProductTypeDropdownItem(productTypeApiItem)
          : null,
      }}
    />
  );
}

export default AddProductFamily;
