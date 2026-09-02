import React from "react";
import Translator from "bazinga-translator";
import { Table } from "react-bootstrap";
import DocumentTranslationLine from "../DocumentTranslationLine/DocumentTranslationLine";
import { getUpdatedTranslateDocumentsInformation } from "../../api/getUpdatedTranslateDocumentsInformation";
import { refreshTranslateDocumentStatus } from "../../api/refreshTranslateDocumentStatus";
import { IDocumentTranslation } from "../../types/IDocumentTranslation";
import { useAppDispatch } from "../../hooks/hooks";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";

interface IProps {
  refreshList?: number;
}
export default function DocumentTranslationList({ refreshList = 0 }: IProps) {
  const dispatch = useAppDispatch();
  const [documentTranslations, setDocumentTranslations] = React.useState<
    Array<IDocumentTranslation>
  >([]);
  const [errorMsg, setErrorMsg] = React.useState<string | null>(null);

  const getDocumentsInformation = async (): Promise<
    Array<IDocumentTranslation>
  > => {
    dispatch(showGlobalLoader(true));
    const response = await getUpdatedTranslateDocumentsInformation();
    dispatch(showGlobalLoader(false));
    if (response.status === 200 && response.data) {
      setDocumentTranslations(response.data);
      return response.data;
    }
    setDocumentTranslations([]);
    setErrorMsg(
      response.message ?? Translator.trans("upload_list.error_refresh")
    );
    return [];
  };

  React.useEffect(() => {
    getDocumentsInformation();
  }, [refreshList]);

  const onDownload = async (documentId: number) => {
    setErrorMsg(null);

    const response = await refreshTranslateDocumentStatus({ documentId });
    if (!(response.status === 200)) {
      setErrorMsg(
        response.message ?? Translator.trans("upload_list.error_refresh")
      );
      return;
    }

    const documentList = await getDocumentsInformation();
    const documentStatus = documentList.find(
      (document) => document.id === documentId
    )?.status;

    if (documentStatus === "ready") {
      setDocumentTranslations((prev) =>
        prev.filter((document) => document.id !== documentId)
      );
      window.location.assign(
        `/en/private/translator_document/${documentId}/download`
      );
    }
  };

  if (!documentTranslations.length) {
    return <div>{Translator.trans("upload_list.table.no_items")}</div>;
  }

  return (
    <div>
      {errorMsg && (
        <div className="alert alert-danger mb-3" role="alert">
          {errorMsg}
        </div>
      )}

      <Table
        hover
        striped
        className="table-responsive report-table tooltip-container"
      >
        <thead>
          <tr>
            <th className="top-label">#</th>
            <th className="top-label">
              {Translator.trans("upload_list.table.original_filename")}
            </th>
            <th className="top-label">
              {Translator.trans("upload.target_language")}
            </th>
            <th className="top-label">
              {Translator.trans("upload.formality")}
            </th>
            <th className="top-label">
              {Translator.trans("upload_list.table.status")}
            </th>
            <th className="top-label">
              {Translator.trans("upload_list.table.waiting_time")}
            </th>
            <th className="top-label">
              {Translator.trans("upload_list.table.size")}
            </th>
            <th className="top-label">Error</th>
            <th className="top-label">
              {Translator.trans("upload_list.table.created_at")}
            </th>
            <th className="top-label">
              {Translator.trans("upload_list.table.action")}
            </th>
          </tr>
        </thead>
        <tbody>
          {documentTranslations.map((documentTranslation) => (
            <DocumentTranslationLine
              key={documentTranslation.id}
              documentTranslation={documentTranslation}
              onDownload={onDownload}
            />
          ))}
        </tbody>
      </Table>
    </div>
  );
}
