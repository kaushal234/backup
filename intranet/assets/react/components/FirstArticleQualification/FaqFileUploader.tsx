import React from "react";
import { useLocation, useParams } from "react-router-dom";
import axios from "axios";
import $ from "jquery";
import { httpClientConfig } from "../../store";
import UppyDropZone from "../../utils/UppyDropZone";

function onSuccess(_: any, location: any) {
  const $div = $("#files");
  if ($div.length) {
    axios
      .get(`${location.pathname}_ajax`)
      .then((response) => {
        $div.html(response.data.trim());
      })
      .catch(() => {
        // emoty on purpose
      });
  }
}
export function FaqFileUploader() {
  const params = useParams();
  const location = useLocation();
  return (
    <UppyDropZone
      url={`${httpClientConfig.baseURL}/quality/first_article_qualifications/${params.id}/files`}
      onSuccess={(response: any) => onSuccess(response, location)}
      maxSize={10000000}
      metaFields={[
        {
          id: "description",
          name: "Description",
          placeholder: "Describe what the file is about",
        },
      ]}
    />
  );
}
