import React, { useEffect } from "react";
import "./CsrSurveyForm.css";
import { useFormField } from "../../hooks/useFormField";
import FormField from "../FormField/FormField";
import { useFormValidator } from "../../hooks/useFormValidator";
import { useFormRadioButtons } from "../../hooks/useFormRadioButtons";
import { IRadioButton } from "../../@type/IRadioButton";
import FormRadioButtons from "../FormRadioButtons/FormRadioButtons";
import { useFormSwitch } from "../../hooks/useFormSwitch";
import FormSwitch from "../FormSwitch/FormSwitch";
import { useFormCheckboxList } from "../../hooks/useFormCheckboxList";
import FormCheckboxList from "../FormCheckboxList/FormCheckboxList";
import { ICsrSurveyFormData } from "../../@type/ICsrSurveyFormData";
import { IAnswerSurveyCustomerServiceRecord } from "../../@type/IGetCustomerServiceRecordResponse";

const RATING_RADIO_OPTIONS: Array<IRadioButton> = [
  {
    id: "0",
    text: "0",
  },
  {
    id: "1",
    text: "1",
  },
  {
    id: "2",
    text: "2",
  },
  {
    id: "3",
    text: "3",
  },
  {
    id: "4",
    text: "4",
  },
  {
    id: "5",
    text: "5",
  },
];

const SHIPPING_OPTIONS: Array<IRadioButton> = [
  {
    id: "no_damages",
    text: "csr_survey_form.question_4.question.options.no_damages",
  },
  {
    id: "transport",
    text: "csr_survey_form.question_4.question.options.transport",
  },
  {
    id: "factory",
    text: "csr_survey_form.question_4.question.options.factory",
  },
];

interface IProps {
  answerSurveyCustomerServiceRecords?: Array<IAnswerSurveyCustomerServiceRecord>;
  onChange: (data: ICsrSurveyFormData) => void;
  submitClickCounter: number;
  resetCounter?: number;
}

export default function CsrSurveyForm(props: IProps) {
  const {
    answerSurveyCustomerServiceRecords = [],
    onChange,
    submitClickCounter,
    resetCounter = 0,
  } = props;

  const question1Value = useFormRadioButtons({
    defaultValue: answerSurveyCustomerServiceRecords?.[0]?.answer ?? "",
    list: RATING_RADIO_OPTIONS,
    requiredError: "csr_survey_form.question_1.question.error.required",
  });

  const question1Comment = useFormField({
    defaultValue: answerSurveyCustomerServiceRecords?.[0]?.comment ?? "",
    requiredError: "csr_survey_form.question_1.comment.error.required",
  });

  const question2Value = useFormRadioButtons({
    defaultValue: answerSurveyCustomerServiceRecords?.[1]?.answer ?? "",
    list: RATING_RADIO_OPTIONS,
    requiredError: "csr_survey_form.question_2.question.error.required",
  });

  const question2Comment = useFormField({
    defaultValue: answerSurveyCustomerServiceRecords?.[1]?.comment ?? "",
    requiredError: "csr_survey_form.question_2.comment.error.required",
  });

  const question3Value = useFormRadioButtons({
    defaultValue: answerSurveyCustomerServiceRecords?.[2]?.answer ?? "",
    list: RATING_RADIO_OPTIONS,
    requiredError: "csr_survey_form.question_3.question.error.required",
  });

  const question3Comment = useFormField({
    defaultValue: answerSurveyCustomerServiceRecords?.[2]?.comment ?? "",
    requiredError: "csr_survey_form.question_3.comment.error.required",
  });

  const question4Value = useFormCheckboxList({
    defaultValue:
      answerSurveyCustomerServiceRecords?.[3]?.answer?.split(",") ?? [],
    list: SHIPPING_OPTIONS,
    requiredError: "csr_survey_form.question_4.question.error.required",
  });

  const question4Comment = useFormField({
    defaultValue: answerSurveyCustomerServiceRecords?.[3]?.comment ?? "",
    requiredError: "csr_survey_form.question_4.comment.error.required",
  });

  const question5Value = useFormSwitch({
    defaultValue:
      `${+(answerSurveyCustomerServiceRecords?.[4]?.answer ?? "")}` === "1",
  });

  const question5Comment = useFormField({
    defaultValue: answerSurveyCustomerServiceRecords?.[4]?.comment ?? "",
  });

  const formValidator = useFormValidator([
    question1Value,
    question1Comment,
    question2Value,
    question2Comment,
    question3Value,
    question3Comment,
    question4Value,
    question4Comment,
    question5Value,
    question5Comment,
  ]);

  useEffect(() => {
    if (submitClickCounter) {
      formValidator.touchAll();
    }
  }, [submitClickCounter]);

  useEffect(() => {
    if (resetCounter) {
      question1Value.reset();
      question1Comment.reset();
      question2Value.reset();
      question2Comment.reset();
      question3Value.reset();
      question3Comment.reset();
      question4Value.reset();
      question4Comment.reset();
      question5Value.reset();
      question5Comment.reset();
    }
  }, [resetCounter]);

  useEffect(() => {
    onChange({
      isFormErrorFree: formValidator.isFormErrorFree,
      isSubmitDisabled: formValidator.isSubmitDisabled,
      data: {
        question1: {
          value: question1Value.value,
          comment: question1Comment.value,
        },
        question2: {
          value: question2Value.value,
          comment: question2Comment.value,
        },
        question3: {
          value: question3Value.value,
          comment: question3Comment.value,
        },
        question4: {
          value: question4Value.value,
          comment: question4Comment.value,
        },
        question5: {
          value: question5Value.value,
          comment: question5Comment.value,
        },
      },
      oldData: answerSurveyCustomerServiceRecords,
    });
  }, [
    formValidator.isFormErrorFree,
    formValidator.isSubmitDisabled,
    question1Value.value,
    question1Comment.value,
    question1Value.value,
    question2Comment.value,
    question2Value.value,
    question3Comment.value,
    question3Value.value,
    question4Comment.value,
    question4Value.value,
    question5Comment.value,
    question5Value.value,
    question1Comment.value,
  ]);

  return (
    <div className="csr_survey_form__wrapper">
      <FormRadioButtons
        {...question1Value.fieldProps}
        requiredLabel
        label="csr_survey_form.question_1.question.title"
        dataCy="csr-survey-form-question-1-question"
        labelPlacement="bottom"
        isCentered
      />
      <FormField
        {...question1Comment.fieldProps}
        label="csr_survey_form.question_1.comment.title"
        requiredLabel
        id="question-1-comment"
        placeholder="csr_survey_form.question_1.comment.placeholder"
        rows={4}
        dataCy="csr-survey-form-question-1-comment"
      />
      <FormRadioButtons
        {...question2Value.fieldProps}
        requiredLabel
        label="csr_survey_form.question_2.question.title"
        dataCy="csr-survey-form-question-2-question"
        labelPlacement="bottom"
        isCentered
      />
      <FormField
        {...question2Comment.fieldProps}
        label="csr_survey_form.question_2.comment.title"
        requiredLabel
        id="question-2-comment"
        placeholder="csr_survey_form.question_2.comment.placeholder"
        rows={4}
        dataCy="csr-survey-form-question-2-comment"
      />
      <FormRadioButtons
        {...question3Value.fieldProps}
        requiredLabel
        label="csr_survey_form.question_3.question.title"
        dataCy="csr-survey-form-question-3-question"
        labelPlacement="bottom"
        isCentered
      />
      <FormField
        {...question3Comment.fieldProps}
        label="csr_survey_form.question_3.comment.title"
        requiredLabel
        id="question-3-comment"
        placeholder="csr_survey_form.question_3.comment.placeholder"
        rows={4}
        dataCy="csr-survey-form-question-3-comment"
      />
      <FormCheckboxList
        {...question4Value.fieldProps}
        requiredLabel
        label="csr_survey_form.question_4.question.title"
        dataCy="csr-survey-form-question-4-question"
      />
      <FormField
        {...question4Comment.fieldProps}
        label="csr_survey_form.question_4.comment.title"
        requiredLabel
        id="question-4-comment"
        placeholder="csr_survey_form.question_4.comment.placeholder"
        rows={4}
        dataCy="csr-survey-form-question-4-comment"
      />
      <FormSwitch
        {...question5Value.fieldProps}
        requiredLabel
        label="csr_survey_form.question_5.question.title"
        dataCy="csr-survey-form-question-5-question"
      />
      <FormField
        {...question5Comment.fieldProps}
        label="csr_survey_form.question_5.comment.title"
        id="question-5-comment"
        placeholder="csr_survey_form.question_5.comment.placeholder"
        rows={4}
        dataCy="csr-survey-form-question-5-comment"
      />
    </div>
  );
}
