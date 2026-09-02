import React from "react";
import axios from "axios";
import { useLocation, useParams } from "react-router-dom";
import $ from "jquery";
import { httpClientConfig } from "../../store";
import UppyDropZone from "../../utils/UppyDropZone";

interface IProps {
  id: any;
}

function onSuccess(_: unknown, location: any) {
  const $div = $("#filesList");
  if ($div.length) {
    axios
      .get(`${location.pathname}_ajax`)
      .then((response) => {
        $div.html(response.data.trim());
      })
      .catch(() => {
        // empty on purpose
      });
  }
}

export function EquipmentShippingRecordFileUploader({ id }: IProps) {
  const params = useParams();
  if (id === undefined) {
    id = params.id;
  }
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/sales/equipment_shipping_records/${id}/files`}
      onSuccess={(response: any) => onSuccess(response, location)}
    />
  );
}
