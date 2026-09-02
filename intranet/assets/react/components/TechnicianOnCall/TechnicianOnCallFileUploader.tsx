import React from "react";
import { httpClientConfig } from "../../store";
import UppyDropZone from "../../utils/UppyDropZone";

type UppyMetaField = {
  id: string;
  name: string;
  placeholder?: string;
  render?: (field: UppyMetaFieldRenderField, h: UppyH) => unknown;
};
type UppyH = (
  tag: string,
  attrs: Record<string, unknown> | null,
  ...children: unknown[]
) => unknown;
type UppyMetaFieldRenderField = {
  value: string;
  onChange: (val: string) => void;
  required: boolean;
  form: string;
};

interface IProps {
  id: any;
  data?: any;
  refreshOnSuccess?: any;
  mainFile?: any;
}

const delay = (ms: any) =>
  new Promise((resolve) => {
    setTimeout(resolve, ms);
  });

const onSuccess = async (location: any, refresh: any, delayMs: any = 0) => {
  if (refresh) {
    await delay(delayMs);
    location.reload();
  }
};

function TechnicianOnCallFileUploader(props: IProps) {
  const { id, data, refreshOnSuccess, mainFile } = props;

  const { location } = window;
  const hint =
    mainFile === true
      ? "JPEG, PNG — max 25 MB"
      : "PDF, Word, Excel, JPEG, PNG, ZIP, PPT, MP4, Outlook — max 25 MB";

  return (
    <>
      <p className="text-muted small mb-1">{hint}</p>
      <UppyDropZone
        url={`${httpClientConfig.baseURL}/service/technician_on_calls/${
          id ?? data?.id
        }/${mainFile ? "main_file" : "files"}`}
        onSuccess={() =>
          onSuccess(location, refreshOnSuccess, mainFile ? 0 : 3000)
        }
        allowedFileTypes={
          mainFile === true
            ? ["image/jpeg", "image/png"]
            : [
                "application/pdf",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "application/vnd.ms-excel",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/octet-stream",
                "image/jpeg",
                "image/png",
                "application/vnd.ms-outlook",
                "application/CDFV2-unknown",
                "application/zip",
                "application/x-zip-compressed",
                "application/vnd.openxmlformats-officedocument.presentationml.presentation",
                "application/vnd.ms-powerpoint",
                "video/mp4",
              ]
        }
        maxSize={25 * 1024 * 1024}
        maxFiles={mainFile ? 1 : 10}
        metaFields={[
          { id: "name", name: "Name", placeholder: "File name" },
          {
            id: "description",
            name: "Description",
            placeholder: "Describe what the file is about",
          },
          {
            id: "public",
            name: "Public",
            render: (field: UppyMetaFieldRenderField, h: UppyH) =>
              h(
                "label",
                null,
                h("input", {
                  type: "checkbox",
                  checked: field.value === "true",
                  onChange: (e: Event) => {
                    const target = e.target as HTMLInputElement;
                    field.onChange(target.checked ? "true" : "false");
                  },
                }),
                " Public"
              ),
          } as unknown as UppyMetaField,
        ]}
      />
    </>
  );
}

export { TechnicianOnCallFileUploader };
