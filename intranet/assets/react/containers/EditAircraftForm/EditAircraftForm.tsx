import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { IAircraft } from "../../types/IGetAllAircraftsResponse";
import AircraftForm from "../../components/AircraftForm/AircraftForm";
import { IAircraftFormData } from "../../types/IAircraftFormData";
import { IPutAircraftApiPayload, putAircraft } from "../../api/putAircraft";
import { getAircraftById } from "../../api/getAircraftById";
import { createManufacturerDropdownItem } from "../../utils/dropdown/manufacturer";

function EditAircraftForm() {
  const { id } = useParams();
  const [data, setData] = useState<IAircraft | null>(null);

  const fetchData = async () => {
    if (id) {
      const response = await getAircraftById({ id });
      if (response.status === 200 && response.data) {
        setData(response.data);
      }
    }
  };

  useEffect(() => {
    fetchData();
  }, [id]);

  const handleSubmit = async (values: IAircraftFormData) => {
    const params: IPutAircraftApiPayload = {
      id: id ?? "",
      name: values.name,
      manufacturer: values.manufacturer.value,
    };
    const response = await putAircraft(params);
    if (response.status === 200) {
      await toastSuccess();
      window.location.href = `/en/private/sales/aircrafts`;
    } else {
      await toastFailure(response.message);
    }
  };

  if (!data) return null;

  return (
    <AircraftForm
      onSubmit={handleSubmit}
      initialValues={{
        name: data.name,
        manufacturer: data.manufacturer
          ? createManufacturerDropdownItem(data.manufacturer)
          : undefined,
      }}
    />
  );
}

export default EditAircraftForm;
