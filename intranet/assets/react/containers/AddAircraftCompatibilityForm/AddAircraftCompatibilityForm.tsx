import React from "react";
import { IAircraftCompatibilityFormData } from "../../types/IAircraftCompatibilityFormData";
import AircraftCompatibilityForm from "../../components/AircraftCompatibilityForm/AircraftCompatibilityForm";
import {
  IPostAircraftCompatibilityApiPayload,
  IPostAircraftCompatibilityFileLineApiPayload,
  postAircraftCompatibility,
} from "../../api/postAircraftCompatibility";
import { toastFailure, toastSuccess } from "../../utils/utils";

function AddAircraftCompatibilityForm() {
  const handleSubmit = async (values: IAircraftCompatibilityFormData) => {
    const files: Array<IPostAircraftCompatibilityFileLineApiPayload> = [];
    (values.files ?? []).forEach(({ file, type }) => {
      files.push({ file, type: type.value });
    });
    const params: IPostAircraftCompatibilityApiPayload = {
      products: (values.products ?? []).map((product) => product.value),
      aircrafts: (values.aircrafts ?? []).map((aircraft) => aircraft.value),
      files,
    };
    const response = await postAircraftCompatibility(params);

    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/aircraft-compatibilities`;
    } else {
      await toastFailure(response.message);
    }
  };

  return <AircraftCompatibilityForm onSubmit={handleSubmit} />;
}

export default AddAircraftCompatibilityForm;
