import { ICsrSurveyFormValues } from "./ICsrSurveyFormValues";
import { IAnswerSurveyCustomerServiceRecord } from "./IGetCustomerServiceRecordResponse";

export interface ICsrSurveyFormData {
  isFormErrorFree: boolean;
  isSubmitDisabled: boolean;
  data: ICsrSurveyFormValues;
  oldData?: Array<IAnswerSurveyCustomerServiceRecord>;
}
