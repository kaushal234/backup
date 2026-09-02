import React, { useEffect } from "react";
import { InjectedFormProps, reduxForm, reset, change, Field } from "redux-form";
import Translator from "bazinga-translator";
import InfoOutlinedIcon from "@mui/icons-material/InfoOutlined";
import { Typography } from "@mui/material";
import Modal from "../Modal/Modal";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { createTocStatusDropdownItem } from "../../utils/dropdown/tocStatus";
import {
  IPutTechnicianOnCallStatusApiPayload,
  putTechnicianOnCallStatus,
} from "../../api/putTechnicianOnCallStatus";
import { setGlobalLoader } from "../../actions/common/loaderActions";
import { decodeHtml, toastFailure, toastSuccess } from "../../utils/utils";
import { TOC_STATUS } from "../../constants";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "../../api/postCommentById";
import { ITechnicianOnCallStatusModalFormData } from "../../types/ITechnicianOnCallStatusModalFormData";
import { validate } from "../../model/form/technician_on_call_status_modal/validation";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import TocStatusParts from "../TocStatusParts/TocStatusParts";
import { ITocStatusPartFormData } from "../../types/ITocStatusPartFormData";
import "./TechnicianOnCallStatusModal.css";
import { getAiClosureSuggestionToc } from "../../api/getAiClosureSuggestionToc";
import { ITechnicianOnCall } from "../../types/IGetTechnicalOnCallsResponse";

const formName = "technician_on_call_status_modal_form";

export interface ITechnicianOnCallStatusModalProps {
  isOpen: boolean;
  onClose: () => void;
  thirdPartyName: string | null;
  tocData: ITechnicianOnCall | null;
}

type IWrappedProps = ITechnicianOnCallStatusModalProps &
  InjectedFormProps<
    ITechnicianOnCallStatusModalFormData,
    ITechnicianOnCallStatusModalProps
  >;

function TechnicianOnCallStatusModal(props: IWrappedProps) {
  const {
    isOpen,
    onClose,
    valid,
    handleSubmit,
    submitting,
    form,
    thirdPartyName,
    tocData,
  } = props;

  const formValues: ITechnicianOnCallStatusModalFormData | undefined =
    useAppSelector((state) => state.form[form].values);

  const dispatch = useAppDispatch();

  const tocId = useAppSelector((state) => state.tocDetail.data?.id);
  const tocIri = useAppSelector((state) => state.tocDetail.data?.["@id"]);
  const tocParts = useAppSelector((state) => state.tocDetail.data?.parts) ?? [];
  const tocSparePartsRequests =
    useAppSelector((state) => state.tocDetail.data?.sparePartsRequests) ?? [];
  const title = useAppSelector((state) => state.tocDetail.data?.title);
  const originalTitle = useAppSelector(
    (state) => state.tocDetail.data?.originalTitle
  );

  const availableStatus = useAppSelector(
    (state) => state.tocDetail.data?.availableStatus
  );

  const statusBlockerMessage = useAppSelector(
    (state) => state.tocDetail.data?.statusBlockerMessage
  );

  const isStatusSuspended = formValues?.status?.value === TOC_STATUS.SUSPENDED;
  const isStatusSolved = formValues?.status?.value === TOC_STATUS.SOLVED;
  const isStatusClosed = formValues?.status?.value === TOC_STATUS.CLOSED;
  const isStatusSolvedWithThirdParty = isStatusSolved && !!thirdPartyName;
  const isMigratedToc =
    tocData?.status === TOC_STATUS.SOLVED &&
    (!tocData.originalSymptoms ||
      !tocData.originalRootCause ||
      !tocData.originalSolution);

  const handleClose = () => {
    onClose();
    dispatch(reset(formName));
  };

  const formatParts = (values?: ITocStatusPartFormData) => {
    if (!values) {
      return [];
    }
    const filteredTocParts = tocParts.filter((part) => values[part["@id"]]);

    const filteredSparePartsRequestParts = tocSparePartsRequests
      .flatMap((obj) => obj.parts || [])
      .filter((part) => values[part["@id"]]);

    return [...filteredTocParts, ...filteredSparePartsRequestParts].map(
      ({ partNumber, description, quantity }) => ({
        partNumber: partNumber ?? "",
        description,
        quantity,
      })
    );
  };

  const onSubmit = async (values: ITechnicianOnCallStatusModalFormData) => {
    const tocParams: IPutTechnicianOnCallStatusApiPayload = {
      id: tocId?.toString() ?? "",
      data: {
        status: values.status?.value,
        ...((isStatusSolved || (isStatusClosed && isMigratedToc)) && {
          originalSymptoms: values.originalSymptoms,
        }),
        ...((isStatusSolved || (isStatusClosed && isMigratedToc)) && {
          originalRootCause: values.originalRootCause,
        }),
        ...((isStatusSolved || (isStatusClosed && isMigratedToc)) && {
          originalSolution: values.originalSolution,
        }),
        ...(isStatusSolved && {
          defectiveParts: formatParts(values.defectiveParts),
        }),
        ...(isStatusSolvedWithThirdParty && {
          thirdPartyJobDescription: values.thirdPartyJobDescription,
        }),
        ...(isStatusSolvedWithThirdParty && {
          thirdPartyHours: values.thirdPartyHours,
        }),
      },
    };

    const commentParams: IPostCommentByTocIdApiPayload = {
      "@id": tocIri ?? "",
      comment: values.reason ?? "",
      public: true,
    };
    dispatch(setGlobalLoader(true));
    const response = await putTechnicianOnCallStatus(tocParams);
    if (isStatusSuspended) {
      await postCommentById(commentParams);
    }
    dispatch(setGlobalLoader(false));
    if (response.data) {
      handleClose();
      await toastSuccess();
      dispatch(setGlobalLoader(true));
      window.location.reload();
    } else {
      await toastFailure(response.errorMessage);
    }
  };

  const handleGenerateSummary = async () => {
    dispatch(setGlobalLoader(true));
    const response = await getAiClosureSuggestionToc({
      id: tocId?.toString() ?? "",
    });

    if (response.data) {
      dispatch(
        change(
          formName,
          "originalSymptoms",
          decodeHtml(response.data.symptoms ?? "")
        )
      );
      dispatch(
        change(
          formName,
          "originalRootCause",
          decodeHtml(response.data.rootCause ?? "")
        )
      );
      dispatch(
        change(
          formName,
          "originalSolution",
          decodeHtml(response.data.solution ?? "")
        )
      );
    } else {
      await toastFailure("Failed to generate AI suggestion");
    }

    dispatch(setGlobalLoader(false));
  };

  useEffect(() => {
    if (isStatusSolved) {
      dispatch(change(formName, "originalSymptoms", originalTitle ?? title));
    }
  }, [isStatusSolved]);

  useEffect(() => {
    if (isStatusClosed) {
      dispatch(
        change(
          formName,
          "originalSymptoms",
          tocData?.originalSolution || originalTitle || title
        )
      );
      dispatch(
        change(formName, "originalRootCause", tocData?.originalRootCause || "")
      );
      dispatch(
        change(formName, "originalSolution", tocData?.originalSolution || "")
      );
    }
  }, [isStatusClosed]);

  useEffect(() => {
    const newPartsFormValue: ITocStatusPartFormData = {};
    tocParts.forEach((part) => {
      newPartsFormValue[part["@id"]] = part.defective;
    });
    dispatch(change(formName, "parts", newPartsFormValue));
  }, [JSON.stringify(tocParts), isOpen]);

  useEffect(() => {
    dispatch(change(formName, "thirdPartyName", thirdPartyName));
  }, [thirdPartyName, isOpen]);

  return (
    <Modal
      className="technician_on_call_status_modal"
      title={Translator.trans("toc.status.modal.title")}
      isOpen={isOpen}
      onClose={handleClose}
    >
      <form onSubmit={handleSubmit(onSubmit)}>
        {statusBlockerMessage?.map((blocker) => {
          const uniqueKey = `${blocker.from}-${blocker.to}-${blocker.message}`;
          return (
            <div
              key={uniqueKey}
              className="technician_on_call_status_modal__solved_warning"
            >
              <InfoOutlinedIcon fontSize="small" />
              <Typography variant="caption" gutterBottom>
                {blocker.message}
              </Typography>
            </div>
          );
        })}
        {availableStatus?.length !== 0 && (
          <div className="row">
            <GenericFormComponent
              type="SingleSelectStaticDropdown"
              label={Translator.trans("toc.status.modal.status")}
              name="status"
              list={(availableStatus ?? []).map((status) =>
                createTocStatusDropdownItem(status)
              )}
              required
            />
          </div>
        )}
        {/*
          Hidden field required so redux-form registers "status" and includes
          its validation error in the form's overall valid/invalid state.
          redux-form only accounts for errors on fields that are actually
          mounted via <Field> — without this, errors.status would exist
          but be silently ignored, and the form would report as valid.
        */}
        {availableStatus?.length === 0 && (
          <Field name="status" component="input" type="hidden" />
        )}

        {availableStatus?.length === 0 &&
          statusBlockerMessage?.length === 0 && (
            <div>{Translator.trans("toc.status.section.error.access")}</div>
          )}

        {isStatusSuspended && (
          <div className="row">
            <GenericFormComponent
              type="Field"
              label={Translator.trans("toc.status.modal.reason")}
              name="reason"
              required
              isTextArea
            />
          </div>
        )}

        {(isStatusSolved || (isStatusClosed && isMigratedToc)) && (
          <>
            <div className="row technician_on_call_status_modal__symptoms">
              <div className="row technician_on_call_status_modal__symptoms_label_wrapper">
                <GenericFormComponent
                  type="Label"
                  label={Translator.trans("toc.status.modal.symptoms")}
                  required
                />
                <button
                  className="btn btn-info"
                  type="button"
                  onClick={handleGenerateSummary}
                >
                  {Translator.trans("toc.status.modal.help")}
                </button>
              </div>
              <GenericFormComponent
                type="Field"
                name="originalSymptoms"
                isTextArea
              />
            </div>
            <div className="row">
              <GenericFormComponent
                type="Field"
                label={Translator.trans("toc.status.modal.root_cause")}
                name="originalRootCause"
                required
                isTextArea
              />
            </div>
            <div className="row">
              <GenericFormComponent
                type="Field"
                label={Translator.trans("toc.status.modal.solution")}
                name="originalSolution"
                required
                isTextArea
              />
            </div>

            {isStatusSolvedWithThirdParty && (
              <>
                <div className="row">
                  <GenericFormComponent
                    type="Field"
                    label={Translator.trans(
                      "toc.status.modal.third_party_job_description"
                    )}
                    name="thirdPartyJobDescription"
                    required
                    isTextArea
                  />
                </div>
                <div className="row">
                  <GenericFormComponent
                    type="Field"
                    label={Translator.trans(
                      "toc.status.modal.third_party_hours"
                    )}
                    name="thirdPartyHours"
                    required
                    allowNumbersOnly
                    normalize={(value: string) =>
                      value ? parseInt(value, 10) : undefined
                    }
                  />
                </div>
              </>
            )}

            {isStatusSolved && <TocStatusParts />}
          </>
        )}

        <div className="row">
          <div className="col text-end">
            <button
              className={`btn btn-${valid ? "info" : "danger"} mt-3`}
              type="submit"
              disabled={submitting || !valid}
            >
              <i className="fa fa-fw fa-save" />
              {Translator.trans("toc.status.modal.submit")}
            </button>
          </div>
        </div>
      </form>
    </Modal>
  );
}

export default reduxForm<
  ITechnicianOnCallStatusModalFormData,
  ITechnicianOnCallStatusModalProps
>({
  form: formName,
  enableReinitialize: true,
  validate,
})(TechnicianOnCallStatusModal);
