import React from "react";
import { Field, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { Button } from "react-bootstrap";
import { renderInlineTextarea } from "../Forms/Elements";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { RatingStars } from "../Forms/RatingStars";
import { putTechnicianOnCallSurvey } from "../../api/putTechnicianOnCallSurvey";
import { postTechnicianOnCallSurvey } from "../../api/postTechnicianOnCallSurvey";
import { ITechnicianOnCallSurvey } from "../../types/IGetTechnicalOnCallSurveyResponse";
import { toastFailure } from "../../utils/utils";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";

type IFormData = {
  execution?: number;
  responsiveness?: number;
  communication?: number;
  attitude?: number;
  comment?: string;
};

interface IProps {
  survey: ITechnicianOnCallSurvey | null;
  onClose: () => void;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function TechnicianOnCallSurveyFormComponent({
  handleSubmit,
  survey,
  onClose,
}: IWrappedProps) {
  const dispatch = useAppDispatch();
  const tocIri = useAppSelector((state) => state.tocDetail.data?.["@id"]);
  const tocId = useAppSelector((state) => state.tocDetail.data?.id);

  const handleFormSurvey = async (values: IFormData) => {
    const submittedTocSurvey = {
      execution: values?.execution ?? 0,
      responsiveness: values?.responsiveness ?? 0,
      communication: values?.communication ?? 0,
      attitude: values?.attitude ?? 0,
      comment: values?.comment ?? "",
      technicianOnCall: survey?.technicianOnCall?.["@id"] ?? tocIri,
    };

    dispatch(showGlobalLoader(true));
    const writeResponse = survey?.id
      ? await putTechnicianOnCallSurvey({
          id: `${survey.id}`,
          data: submittedTocSurvey,
        })
      : await postTechnicianOnCallSurvey(submittedTocSurvey);

    if (writeResponse.status === 200) {
      await dispatch(fetchTechnicianOnCallThunk({ tocId: `${tocId}` }));
      dispatch(showGlobalLoader(false));
      onClose();
    } else {
      dispatch(showGlobalLoader(false));
      await toastFailure(writeResponse.message);
    }
  };

  return (
    <>
      <form onSubmit={handleSubmit(handleFormSurvey)}>
        <div className="row justify-content-md-center">
          <div className="col col-8 row">
            <div className="mb-3">
              <label className="col-form-label undefined custom_label">
                {Translator.trans("toc.fields.survey.execution")}
              </label>
              <Field
                name="execution"
                component={RatingStars}
                max={5}
                aria-label={Translator.trans("toc.fields.survey.execution")}
              />
            </div>
            <div className="mb-3">
              <label className="col-form-label undefined custom_label">
                {Translator.trans("toc.fields.survey.responsiveness")}
              </label>
              <Field
                name="responsiveness"
                component={RatingStars}
                max={5}
                aria-label={Translator.trans(
                  "toc.fields.survey.responsiveness"
                )}
              />
            </div>
            <div className="mb-3">
              <label className="col-form-label undefined custom_label">
                {Translator.trans("toc.fields.survey.communication")}
              </label>
              <Field
                name="communication"
                component={RatingStars}
                max={5}
                aria-label={Translator.trans("toc.fields.survey.communication")}
              />
            </div>
            <div className="mb-3">
              <label className="col-form-label undefined custom_label">
                {Translator.trans("toc.fields.survey.attitude")}
              </label>
              <Field
                name="attitude"
                component={RatingStars}
                max={5}
                aria-label={Translator.trans("toc.fields.survey.attitude")}
              />
            </div>
            <Field
              name="comment"
              label={Translator.trans("toc.fields.parts.comment")}
              component={renderInlineTextarea}
            />
          </div>
        </div>

        <div className="d-flex justify-content-around align-items-center mt-2">
          <div>
            <Button
              variant="primary"
              title={Translator.trans("back")}
              type="button"
              onClick={() => onClose()}
            >
              <i className="fa-solid fa-chevron-left" />
            </Button>
          </div>
          <div>
            <Button
              variant="info"
              title={
                survey?.id
                  ? Translator.trans("toc.button.survey.edit")
                  : Translator.trans("toc.button.survey.create")
              }
              type="submit"
            >
              {survey?.id
                ? Translator.trans("toc.button.survey.edit")
                : Translator.trans("toc.button.survey.create")}
            </Button>
          </div>
        </div>
      </form>

      <hr />
    </>
  );
}

const TechnicianOnCallSurveyForm = reduxForm<IFormData, IProps>({
  form: "toc_survey",
})(TechnicianOnCallSurveyFormComponent);
export { TechnicianOnCallSurveyForm };
