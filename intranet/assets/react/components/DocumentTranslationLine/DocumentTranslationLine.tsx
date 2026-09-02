import React from "react";
import moment from "moment";
import Translator from "bazinga-translator";
import { IDocumentTranslation } from "../../types/IDocumentTranslation";
import "./DocumentTranslationLine.css";
import { convertToTitleCase } from "../../utils/utils";

interface IProps {
  documentTranslation: IDocumentTranslation;
  onDownload: (id: number) => void;
}

function getBadgeClass(status: string) {
  switch (status) {
    case "ready":
      return "badge bg-success text-white";
    case "queued":
      return "badge bg-warning text-white";
    case "failed":
      return "badge bg-danger text-dark";
    case "downloaded":
      return "badge bg-info text-white";
    default:
      return "badge bg-secondary text-white";
  }
}

function formatFileSizeFR(sizeBytes: number): string {
  if (!Number.isFinite(sizeBytes) || sizeBytes < 0) return "";

  const KO = 1024;
  const MO = 1024 * 1024;

  const formatNumber = (n: number): string => {
    return n.toFixed(2);
  };

  if (sizeBytes < KO) {
    return `${Math.round(sizeBytes)} o`;
  }
  if (sizeBytes < MO) {
    return `${formatNumber(sizeBytes / KO)} ko`;
  }
  return `${formatNumber(sizeBytes / MO)} Mo`;
}

export default function DocumentTranslationLine({
  documentTranslation,
  onDownload,
}: IProps) {
  return (
    <tr>
      <td>{documentTranslation.id}</td>
      <td>
        <span
          title={documentTranslation.filename}
          className="translator_line__ellipsis"
        >
          {documentTranslation.filename}
        </span>
      </td>
      <td>{(documentTranslation.targetLang || "").toUpperCase()}</td>
      <td>{convertToTitleCase(documentTranslation.formality || "")}</td>
      <td>
        <span
          className={`${getBadgeClass(
            documentTranslation.status
          )} translator_line__status_badge`}
        >
          {documentTranslation.status.toUpperCase()}
        </span>
      </td>
      <td>
        {documentTranslation.estimatedSeconds
          ? `${documentTranslation.estimatedSeconds} sec`
          : ""}
      </td>
      <td>{formatFileSizeFR(documentTranslation.size ?? 0)}</td>
      <td>
        <span
          title={documentTranslation.errorMessage || ""}
          className="translator_line__ellipsis"
        >
          {documentTranslation.errorMessage || ""}
        </span>
      </td>
      <td>
        {documentTranslation.createdAt &&
        moment(documentTranslation.createdAt).isValid()
          ? moment(documentTranslation.createdAt).format("YYYY-MM-DD")
          : ""}
      </td>
      <td>
        <button
          type="button"
          className="btn btn-link p-0 d-inline-flex align-items-center justify-content-center translator_line__btn_icon"
          onClick={() => onDownload(documentTranslation.id ?? 0)}
          aria-label="Download"
          title={Translator.trans("upload_list.table.download_button_title")}
        >
          <i className="fa fa-download" aria-hidden="true" />
        </button>
      </td>
    </tr>
  );
}
