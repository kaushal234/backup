import React from "react";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { IAircraftFormData } from "../../types/IAircraftFormData";
import { IPostAircraftApiPayload, postAircraft } from "../../api/postAircraft";
import AircraftForm from "../../components/AircraftForm/AircraftForm";

function AddAircraftForm() {
  const handleSubmit = async (values: IAircraftFormData) => {
    const params: IPostAircraftApiPayload = {
      name: values.name,
      manufacturer: values.manufacturer.value,
    };
    const response = await postAircraft(params);

    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/aircrafts`;
    } else {
      await toastFailure(response.message);
    }
  };

  return <AircraftForm onSubmit={handleSubmit} />;
}

export default AddAircraftForm;
