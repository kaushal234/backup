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
      .get(`${location.pathname}/files_ajax`)
      .then((response) => {
        $div.html(response.data.trim());
      })
      .catch(() => {
        // empty on purpose
      });
  }
}

export function SparePartsRequestFileUploader({ id }: IProps) {
  const params = useParams();
  const idParam: number = id ?? params.id;
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/parts/spare_parts_requests/${idParam}/files`}
      onSuccess={(response: any) => onSuccess(response, location)}
      metaFields={[
        { id: "name", name: "Name", placeholder: "File name" },
        {
          id: "description",
          name: "Description",
          placeholder: "Describe what the file is about",
        },
      ]}
      allowedFileTypes={[
        "application/zip",
        "image/jpeg",
        "image/png",
        "image/svg",
        "application/x-zip-compressed",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.ms-powerpoint",
        "video/mp4",
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      ]}
    />
  );
}
