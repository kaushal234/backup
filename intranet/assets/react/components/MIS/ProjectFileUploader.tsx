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
  const $div = $("#files_tab");
  if ($div.length) {
    axios
      .get(`${location.pathname}_ajax`)
      .then((response) => {
        $div.html(response.data.trim());
      })
      .catch(() => {});
  }
}

export function ProjectFileUploader({ id }: IProps) {
  if (id === undefined) {
    const params = useParams();
    id = params.id;
  }
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/mis/projects/${id}/files`}
      onSuccess={(response: any) => onSuccess(response, location)}
      extraAllowedFileTypes={[
        "video/mp4",
        "video/webm",
        "video/x-matroska",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.ms-powerpoint",
      ]}
    />
  );
}
