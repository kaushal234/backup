import React from "react";
import { useParams } from "react-router";
import { IAircraftCompatibilityFileFormData } from "../../types/IAircraftCompatibilityFormData";
import { toastFailure, toastSuccess } from "../../utils/utils";
import {
  IPostAircraftCompatibilityFileApiPayload,
  postAircraftCompatibilityFile,
} from "../../api/postAircraftCompatibilityFile";
import AircraftCompatibilityFileForm from "../../components/AircraftCompatibilityFileForm/AircraftCompatibilityFileForm";

function AddAircraftCompatibilityFileForm() {
  const { id } = useParams();

  if (!id) return null;

  const handleSubmit = async (values: IAircraftCompatibilityFileFormData) => {
    const params: IPostAircraftCompatibilityFileApiPayload = {
      id: parseInt(id, 10),
      file: values.file,
      type: values.type.value,
    };
    const response = await postAircraftCompatibilityFile(params);

    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/aircraft-compatibilities/${id}/show`;
    } else {
      await toastFailure(response.message);
    }
  };

  return <AircraftCompatibilityFileForm onSubmit={handleSubmit} />;
}

export default AddAircraftCompatibilityFileForm;
