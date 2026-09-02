import { TOC_STATUS } from "../../../constants";
import { ITechnicianOnCallStatusModalFormData } from "../../../types/ITechnicianOnCallStatusModalFormData";
import { ITechnicianOnCallStatusModalFormErrors } from "../../../types/ITechnicianOnCallStatusModalFormErrors";
import { ITechnicianOnCallStatusModalProps } from "../../../components/TechnicianOnCallStatusModal/TechnicianOnCallStatusModal";

export const validate = (
  values: ITechnicianOnCallStatusModalFormData,
  props: ITechnicianOnCallStatusModalProps
) => {
  const errors: ITechnicianOnCallStatusModalFormErrors = {};

  const { tocData } = props;

  const isStatusSolved = values?.status?.value === TOC_STATUS.SOLVED;
  const isStatusClosed = values?.status?.value === TOC_STATUS.CLOSED;
  const isMigratedToc =
    tocData?.status === TOC_STATUS.SOLVED &&
    (!tocData.originalSymptoms ||
      !tocData.originalRootCause ||
      !tocData.originalSolution);

  if (!values.status) {
    errors.status = "Required";
  }

  if (values.status?.value === TOC_STATUS.SUSPENDED && !values.reason) {
    errors.reason = "Required";
  }

  if (
    (isStatusSolved || (isStatusClosed && isMigratedToc)) &&
    !values.originalSymptoms
  ) {
    errors.originalSymptoms = "Required";
  }

  if (
    (isStatusSolved || (isStatusClosed && isMigratedToc)) &&
    !values.originalRootCause
  ) {
    errors.originalRootCause = "Required";
  }

  if (
    (isStatusSolved || (isStatusClosed && isMigratedToc)) &&
    !values.originalSolution
  ) {
    errors.originalSolution = "Required";
  }

  if (
    values.status?.value === TOC_STATUS.SOLVED &&
    values.thirdPartyName &&
    !values.thirdPartyJobDescription
  ) {
    errors.thirdPartyJobDescription = "Required";
  }

  if (
    values.status?.value === TOC_STATUS.SOLVED &&
    values.thirdPartyName &&
    (values.thirdPartyHours === null ||
      values.thirdPartyHours === undefined ||
      values.thirdPartyHours < 0)
  ) {
    errors.thirdPartyHours = "Required, Must be positive";
  }

  return errors;
};
