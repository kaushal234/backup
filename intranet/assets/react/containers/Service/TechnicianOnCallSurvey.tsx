import React, { useEffect, useState } from "react";
import { Alert, Button, Card } from "react-bootstrap";
import Translator from "bazinga-translator";
import Rating from "@mui/material/Rating";
import { CardContent, Typography } from "@mui/material";
import { useAppSelector } from "../../hooks/hooks";
import { TechnicianOnCallSurveyForm } from "../../components/Service/TechnicianOnCallSurveyForm";
import { getTechnicianOnCallSurveyById } from "../../api/getTechnicianOnCallSurveyById";
import { ITechnicianOnCallSurvey } from "../../types/IGetTechnicalOnCallSurveyResponse";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";
import "./TechnicianOnCallSurvey.css";

interface IProps {
  isGrantedWrite: boolean;
}
function TechnicianOnCallSurvey(props: IProps) {
  const { isGrantedWrite = false } = props;
  const tocSurvey = useAppSelector((state) => state.tocDetail.data?.survey);
  const [data, setData] = useState<ITechnicianOnCallSurvey | null>();
  const [showForm, setShowForm] = useState<boolean>(false);

  const fetchData = async () => {
    if (tocSurvey === undefined) {
      return;
    }

    if (!tocSurvey?.id) {
      setData(null);
    } else {
      const response = await getTechnicianOnCallSurveyById({
        tocSurveyId: `${tocSurvey.id}`,
      });

      if (response.data) {
        setData(response.data);
      } else {
        setData(null);
      }
    }
  };

  useEffect(() => {
    fetchData();
  }, [tocSurvey]);

  if (data === undefined) {
    return <FullScreenLoader />;
  }

  return (
    <>
      <FullScreenLoader />

      {!tocSurvey && !showForm && (
        <Alert variant="warning" className="d-flex justify-content-between">
          <span className="align-self-center">
            {Translator.trans("toc.messages.errors.no_survey")}
          </span>
          {isGrantedWrite && (
            <Button
              variant="warning"
              title={Translator.trans("menu.edit")}
              type="button"
              onClick={() => setShowForm(true)}
            >
              <i className="fa fa-pencil-alt" />
            </Button>
          )}
        </Alert>
      )}

      {tocSurvey && !showForm && (
        <div className="row justify-content-md-center">
          <div className="col col-8 row">
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h3 className="mb-0">
                {Translator.trans("toc.title.survey.note")}
              </h3>
              {isGrantedWrite && (
                <Button
                  variant="warning"
                  title={Translator.trans("menu.edit")}
                  type="button"
                  onClick={() => setShowForm(true)}
                >
                  <i className="fa fa-pencil-alt" />
                </Button>
              )}
            </div>
            <div className="mb-3">
              <Typography component="legend">
                {Translator.trans("toc.fields.survey.execution")}
              </Typography>
              <Rating
                name="read-only"
                value={tocSurvey.execution}
                readOnly
                className="technician_on_call_survey__custom_rating"
              />
            </div>
            <div className="mb-3">
              <Typography component="legend">
                {Translator.trans("toc.fields.survey.responsiveness")}
              </Typography>
              <Rating
                name="read-only"
                value={tocSurvey.responsiveness}
                readOnly
                className="technician_on_call_survey__custom_rating"
              />
            </div>
            <div className="mb-3">
              <Typography component="legend">
                {Translator.trans("toc.fields.survey.communication")}
              </Typography>
              <Rating
                name="read-only"
                value={tocSurvey.communication}
                readOnly
                className="technician_on_call_survey__custom_rating"
              />
            </div>
            <div className="mb-3">
              <Typography component="legend">
                {Translator.trans("toc.fields.survey.attitude")}
              </Typography>
              <Rating
                name="read-only"
                value={tocSurvey.attitude}
                readOnly
                className="technician_on_call_survey__custom_rating"
              />
            </div>
            <Card className="mb-3">
              <CardContent>
                <h3>{Translator.trans("toc.title.survey.comment")}</h3>
                <div>{tocSurvey.comment}</div>
              </CardContent>
            </Card>
          </div>
        </div>
      )}

      {showForm && isGrantedWrite && (
        <TechnicianOnCallSurveyForm
          key={Math.random()}
          survey={data}
          onClose={() => setShowForm(false)}
          initialValues={{
            execution: data?.execution ?? 0,
            responsiveness: data?.responsiveness ?? 0,
            communication: data?.communication ?? 0,
            attitude: data?.attitude ?? 0,
            comment: data?.comment ?? "",
          }}
        />
      )}
    </>
  );
}

export { TechnicianOnCallSurvey };
