import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { Button, Container, Row } from "react-bootstrap";
import Col from "react-bootstrap/Col";
import Form from "react-bootstrap/Form";
import Loader from "../../components/Loader";
import {
  getCommentsMapping,
  getLogsMapping,
  isActivityResultFetched,
  isCreationPending,
} from "../../selectors/activity/activitySelector";
import {
  createComment as createCommentAction,
  getComments as getCommentsAction,
} from "../../actions/activity/commentsActions";
import ActivityListUserStory from "../../components/Activity/ActivityListUserStory";
import { getLogs as getLogsAction } from "../../actions/activity/logsActions";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  resource: any;
  getComments?: any;
  comments?: any;
  getLogs?: any;
  logs?: any;
  creationIsPending?: any;
  activityResultFetched?: any;
  createComment?: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function ActivityUserStory({
  resource,
  getComments,
  comments,
  getLogs,
  logs,
  creationIsPending,
  activityResultFetched,
  createComment,
  handleSubmit,
  reset,
}: IWrappedProps) {
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [showComments, setShowComments] = useState(true);
  const [showLogs, setShowLogs] = useState(false);

  useEffect(() => {
    getComments(resource);
    getLogs(resource);
  }, [comments, resource, getComments]);

  const onSubmit = (formData: any) => {
    createComment(resource, formData.comment);
    reset();
    setShowCreateForm(false);
  };

  const activity = [...(comments || []), ...(logs || [])];

  activity.sort((item1, item2) => {
    return item2.createdAt - item1.createdAt;
  });

  return (
    <Container className="p-0">
      <Row className="flex px-3">
        <Col>
          <Button
            type="button"
            variant={`${showCreateForm ? "danger" : "info"}`}
            className="p-0 w-100 h-100"
            onClick={() => {
              setShowCreateForm(!showCreateForm);
              setShowLogs(false);
              setShowComments(true);
            }}
          >
            <i className={`fa fa-${showCreateForm ? "close" : "plus"}`} />
            &nbsp;
            {showCreateForm
              ? Translator.trans("activity.comment.close")
              : Translator.trans("activity.comment.add")}
          </Button>
        </Col>
        <Col>
          <Button
            type="button"
            variant={`${showLogs ? "danger" : "info"}`}
            className="p-0 w-100 h-100"
            onClick={() => {
              setShowLogs(!showLogs);
              setShowCreateForm(false);
              setShowComments(false);
            }}
          >
            <i className={`fa fa-${showLogs ? "close" : "plus"}`} />
            &nbsp;
            {showLogs
              ? Translator.trans("activity.log.hide")
              : Translator.trans("activity.log.show")}
          </Button>
        </Col>
      </Row>

      <Row>
        {showCreateForm && (
          <Col>
            {creationIsPending && (
              <Loader childStyle={{ height: "30px", paddingTop: 10 }} />
            )}
            <Form onSubmit={handleSubmit(onSubmit)}>
              {!creationIsPending && (
                <>
                  <GenericFormComponent
                    type="Field"
                    name="comment"
                    required
                    label={Translator.trans("activity.comment.comment")}
                  />
                  <div>
                    <Button
                      type="submit"
                      className="p-1 ps-2 pe-2"
                      variant="info"
                    >
                      {Translator.trans("activity.comment.submit")}
                    </Button>
                  </div>
                </>
              )}
            </Form>
          </Col>
        )}
      </Row>
      {!activityResultFetched && <Loader />}
      {activityResultFetched && (
        <ActivityListUserStory
          items={activity}
          showLogs={showLogs}
          showComments={showComments}
        />
      )}
    </Container>
  );
}

const formConfiguration = {
  form: "comment_form",
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { resource } = props;
  const comments = getCommentsMapping(state, resource);
  const logs = getLogsMapping(state, resource);
  const creationIsPending = isCreationPending(state, resource);
  const activityResultFetched = isActivityResultFetched(state, resource);

  return {
    resource,
    comments,
    logs,
    creationIsPending,
    activityResultFetched,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    getComments: (resource: any) => dispatch(getCommentsAction(resource)),
    getLogs: (ressource: any) => dispatch(getLogsAction(ressource)),
    createComment: (resource: any, message: any, file: any) =>
      dispatch(createCommentAction(resource, message, file)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(ActivityUserStory));
