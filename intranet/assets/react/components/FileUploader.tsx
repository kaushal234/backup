import React from "react";
import axios from "axios";
import { useLocation, useParams } from "react-router-dom";
import $ from "jquery";
import { httpClientConfig } from "../store";
import UppyDropZone from "../utils/UppyDropZone";

function onSuccess(_: unknown, location: any) {
  const $div = $("#files");
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

export function FileUploader() {
  const params = useParams();
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/sales/orders/${params.id}/files`}
      onSuccess={(response: any) => onSuccess(response, location)}
    />
  );
}
