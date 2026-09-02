import React from "react";
import Uppy from "@uppy/core";
import { Dashboard } from "@uppy/react";
import DashboardStyle from "@uppy/dashboard";
import DragDrop from "@uppy/drag-drop";
import XHR from "@uppy/xhr-upload";
import { Card } from "react-bootstrap";
import Webcam from "@uppy/webcam";
import ImageEditor from "@uppy/image-editor";
import _ from "lodash";
import French from "@uppy/locales/lib/fr_FR";
import Chinese from "@uppy/locales/lib/zh_CN";
import English from "@uppy/locales/lib/en_US";
import ScreenCapture from "@uppy/screen-capture";
import { httpClientConfig } from "../store";

interface MetaField {
  id: string;
  name: string;
  value?: string;
  placeholder?: string;
}

interface IProps {
  allowedFileTypes?: Array<string> | null;
  extraAllowedFileTypes?: Array<string> | null;
  maxSize?: number;
  maxFiles?: number;
  url: string;
  onSuccess: (response: any) => void;
  metaFields?: MetaField[];
  fieldName?: string;
}

export default class UppyDropZone extends React.Component<IProps> {
  defaultAllowedFileTypes: Array<string>;

  allowedFileTypes: Array<string>;

  uppy: any;

  metaFields: any;

  constructor(props: IProps) {
    super(props);

    const {
      allowedFileTypes,
      extraAllowedFileTypes,
      maxSize,
      maxFiles,
      url,
      onSuccess,
      metaFields,
      fieldName,
    } = this.props;

    const lang = $("html").attr("lang");
    const localeMap: Record<string, any> = {
      fr: French,
      "zh-CN": Chinese,
    };

    this.defaultAllowedFileTypes = [
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      "application/vnd.ms-excel",
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      "application/octet-stream",
      "image/jpeg",
      "image/png",
      ".ppt",
      ".pptx",
      "application/zip",
      "application/x-zip-compressed",
      "application/vnd.ms-outlook",
      "application/CDFV2-unknown",
      "message/rfc822",
    ];

    this.allowedFileTypes = allowedFileTypes ?? this.defaultAllowedFileTypes;
    this.metaFields = metaFields ?? null;

    if (extraAllowedFileTypes) {
      this.allowedFileTypes = [
        ...this.allowedFileTypes,
        ...extraAllowedFileTypes,
      ];
    }

    this.uppy = new Uppy({
      restrictions: {
        maxFileSize: maxSize ?? 7000000,
        maxNumberOfFiles: maxFiles ?? 5,
        allowedFileTypes: this.allowedFileTypes,
      },
      locale: localeMap[lang ?? ""] ?? English,
    });

    this.uppy.use(DashboardStyle);
    this.uppy.use(DragDrop);
    this.uppy.use(ScreenCapture, {
      target: DashboardStyle,
      preferredVideoMimeType: "video/webm",
    });
    this.uppy.use(Webcam, { modes: ["picture"] });
    this.uppy.use(ImageEditor, { target: DashboardStyle });
    this.uppy.use(XHR, {
      endpoint: url,
      fieldName: fieldName ?? "file",
      headers: {
        authorization: httpClientConfig.headers.Authorization,
      },
      getResponseData: (responseText: string) => {
        try {
          return JSON.parse(responseText);
        } catch {
          return {};
        }
      },
    });

    this.uppy.on("upload-success", (_file: any, response: any) => {
      const { body } = response;

      if (_.has(body, "violations")) {
        this.uppy.info(
          body.violations.map((violation: any) => violation.message).join(" "),
          "error",
          10000
        );
      } else {
        onSuccess(body);
      }
    });
  }

  render() {
    return (
      <Card>
        <Dashboard
          metaFields={this.metaFields}
          uppy={this.uppy}
          plugins={["DashboardStyle", "DragDrop", "Webcam"]}
          width="100%"
          proudlyDisplayPoweredByUppy={false}
          height="fit-content"
        />
      </Card>
    );
  }
}
