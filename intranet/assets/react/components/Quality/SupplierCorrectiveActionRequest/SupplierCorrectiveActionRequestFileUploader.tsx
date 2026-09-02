import React from "react";
import { useLocation, useParams } from "react-router-dom";
import $ from "jquery";
import axios from "axios";
import { httpClientConfig } from "../../../store";
import UppyDropZone from "../../../utils/UppyDropZone";

interface ILocation {
  pathname: string;
  search: string;
  hash: string;
  state: boolean;
  key: string;
}

interface ISupplierCorrectiveActionRequestFileUploader {
  id?: number | null;
}

function onSuccess(location: ILocation) {
  const $div = $("#files_tab");
  if ($div.length) {
    axios
      .get(`${location.pathname}_ajax`)
      .then((ajaxResponse) => {
        $div.html(ajaxResponse.data.trim());
      })
      .catch((error) => {
        console.error("Failed to load files tab: ", error);
      });
  }
}

export function SupplierCorrectiveActionRequestFileUploader({
  id,
}: ISupplierCorrectiveActionRequestFileUploader) {
  const params = useParams();
  const effectiveId = id ?? Number(params.id);
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/quality/supplier_corrective_action_requests/${effectiveId}/main_file`}
      onSuccess={() => onSuccess(location)}
      extraAllowedFileTypes={["video/mp4", "video/webm", "video/x-matroska"]}
      maxFiles={1}
    />
  );
}
