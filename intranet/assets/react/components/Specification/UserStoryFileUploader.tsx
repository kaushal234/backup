import React from "react";
import axios from "axios";
import { useParams } from "react-router-dom";
import $ from "jquery";
import { httpClientConfig } from "../../store";
import UppyDropZone from "../../utils/UppyDropZone";

interface IProps {
  id: any;
}

function onSuccess() {
  const $div = $("#files");
  if ($div.length) {
    axios
      .get(`${window.location.pathname}_ajax`)
      .then((response) => {
        $div.html(response.data.trim());
      })
      .catch(() => {
        /* empty on purpose */
      });
  }
}
export function UserStoryFileUploader({ id }: IProps) {
  const params = useParams();
  if (id === undefined) {
    id = params.id;
  }

  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/mis/user_stories/${id}/files`}
      onSuccess={() => onSuccess()}
      allowedFileTypes={["application/pdf", "image/jpeg", "image/png"]}
      maxSize={25000000}
    />
  );
}
