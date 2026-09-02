import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { IAircraftCompatibilityFormData } from "../../types/IAircraftCompatibilityFormData";
import AircraftCompatibilityForm from "../../components/AircraftCompatibilityForm/AircraftCompatibilityForm";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { IAircraftCompatibility } from "../../types/IGetAllAircraftCompatibilitiesResponse";
import { getAircraftCompatibilityById } from "../../api/getAircraftCompatibilityById";
import { createProductDropdownItem } from "../../utils/dropdown/product";
import { createAircraftDropdownItem } from "../../utils/dropdown/aircraft";
import {
  IPutAircraftCompatibilityApiPayload,
  putAircraftCompatibility,
} from "../../api/putAircraftCompatibility";

function EditAircraftCompatibilityForm() {
  const { id } = useParams();
  const [data, setData] = useState<IAircraftCompatibility | null>(null);

  const fetchData = async () => {
    if (id) {
      const response = await getAircraftCompatibilityById({ id });
      if (response.status === 200 && response.data) {
        setData(response.data);
      }
    }
  };

  useEffect(() => {
    fetchData();
  }, [id]);

  const handleSubmit = async (values: IAircraftCompatibilityFormData) => {
    const params: IPutAircraftCompatibilityApiPayload = {
      id: id ?? "",
      products: (values.products ?? []).map((product) => product.value),
      aircrafts: (values.aircrafts ?? []).map((aircraft) => aircraft.value),
    };
    const response = await putAircraftCompatibility(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/aircraft-compatibilities`;
    } else {
      await toastFailure(response.message);
    }
  };

  if (!data) return null;

  return (
    <AircraftCompatibilityForm
      onSubmit={handleSubmit}
      initialValues={{
        products: data.products.map((item) => createProductDropdownItem(item)),
        aircrafts: data.aircrafts.map((item) =>
          createAircraftDropdownItem(item)
        ),
      }}
      isEdit
    />
  );
}

export default EditAircraftCompatibilityForm;
