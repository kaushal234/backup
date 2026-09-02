import Translator from "bazinga-translator";
import { Col } from "react-bootstrap";
import React from "react";
import { enqueueSnackbar } from "notistack";
import { SupplierCorrectiveActionRequestFileUploader } from "./SupplierCorrectiveActionRequestFileUploader";
import { ISupplierCorrectiveActionRequestMainFile } from "../../../types/ISupplierCorrectiveActionRequestPropsApi";
import { deleteSupplierCorrectiveActionRequestMainFile } from "../../../api/deleteSupplierCorrectiveActionRequestMainFile";

interface ISupplierCorrectiveActionRequestFile {
  id?: number | null;
  mainFile?: ISupplierCorrectiveActionRequestMainFile | null;
}

export function SupplierCorrectiveActionRequestFile({
  id,
  mainFile,
}: ISupplierCorrectiveActionRequestFile) {
  const [isMainFileDeleted, setIsMainFileDeleted] = React.useState(false);
  const [isLoading, setIsLoading] = React.useState(false);

  const handleDelete = async () => {
    setIsLoading(true);
    await deleteSupplierCorrectiveActionRequestMainFile({
      id: id?.toString() || "",
      mainFileId: mainFile?.id?.toString() || "",
    });
    setIsLoading(false);
    setIsMainFileDeleted(true);
    enqueueSnackbar(
      Translator.trans(
        "supplier_corrective_action_request.toast.main_file_deleted"
      )
    );
  };

  if (mainFile && !isMainFileDeleted) {
    return (
      <Col md={6}>
        <div className="card">
          <div className="card-header">
            <h3>
              {Translator.trans("supplier_corrective_action_request.main_file")}
            </h3>
          </div>
          <div className="ibox-content text-center">
            {mainFile.mimeType.includes("image") ? (
              <a
                href={`/en/private/quality/supplier-corrective-action-requests/${id}/main-file/${mainFile.id}`}
              >
                <img
                  alt="Supplier Corrective Action Request Main File"
                  className="m-t-xs img-fluid"
                  width="600"
                  src={`/en/private/uploads/${mainFile.filePath}`}
                />
              </a>
            ) : (
              <a
                href={`/en/private/quality/supplier-corrective-action-requests/${id}/main-file/${mainFile.id}`}
              >
                <img
                  alt="Supplier Corrective Action Request Main File"
                  src="/shared/bluesphere/96x96/mimetypes/document.png"
                />
              </a>
            )}

            <div
              className="text-center d-flex justify-content-center align-items-center"
              style={{ marginTop: "16px" }}
            >
              <button
                onClick={handleDelete}
                className="btn btn-danger text-capitalize m-b-xl me-3"
                type="submit"
                disabled={isLoading}
              >
                <i className="fa fa-fa fa-trash" />
                &nbsp;
                {isLoading
                  ? Translator.trans(
                      "supplier_corrective_action_request.button.deleting"
                    )
                  : Translator.trans("button.delete")}
              </button>
              <a
                href={`/en/private/quality/supplier-corrective-action-requests/${id}/show`}
                className="btn btn-info m-b-xl"
                type="submit"
              >
                &nbsp;
                {Translator.trans("button.finish")}
              </a>
            </div>
          </div>
        </div>
      </Col>
    );
  }

  return (
    <Col md={6}>
      <div className="card">
        <div className="card-header">
          <h3>
            {Translator.trans(
              "supplier_corrective_action_request.button.add_file"
            )}
          </h3>
        </div>
        <div className="ibox-content">
          <SupplierCorrectiveActionRequestFileUploader id={id} />
          <div className="text-center" style={{ marginTop: "5px" }}>
            <a
              href={`/en/private/quality/supplier-corrective-action-requests/${id}/show`}
              className="btn btn-info m-b-xl"
              type="submit"
            >
              &nbsp;{Translator.trans("button.finish")}
            </a>
          </div>
        </div>
      </div>
    </Col>
  );
}
