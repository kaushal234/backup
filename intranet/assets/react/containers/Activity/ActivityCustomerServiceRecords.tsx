import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { Button, Col, Container, Form, Row } from "react-bootstrap";
import {
  createComment as createCommentAction,
  getComments as getCommentsAction,
} from "../../actions/activity/commentsActions";
import {
  getCommentsMapping,
  isActivityResultFetched,
  isCreationPending,
} from "../../selectors/activity/activitySelector";
import Loader from "../../components/Loader";
import ActivityListCustomerServiceRecords from "../../components/Activity/ActivityListCustomerServiceRecords";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  hideComments: any;
  comments: any;
  getComments: any;
  resource: any;
  createComment: any;
  showCommentForm: any;
  creationIsPending: any;
  activityResultFetched: any;
}

interface IState {
  showCreateForm: any;
  hideComments: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class ActivityCustomerServiceRecords extends React.Component<
  IWrappedProps,
  IState
> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      hideComments: null,
      showCreateForm: window.innerWidth >= 992,
    };
  }

  componentDidMount() {
    const { hideComments, comments, getComments, resource } = this.props;
    if (!hideComments && comments === null) {
      getComments(resource);
    }
  }

  handleSubmit(resource: any, data: any) {
    const { createComment } = this.props;
    createComment(resource, data.comment);

    // Clear form
    data.comment = "";
  }

  render() {
    const {
      resource,
      hideComments,
      showCommentForm,
      comments,
      handleSubmit,
      creationIsPending,
      activityResultFetched,
    } = this.props;

    const { showCreateForm, hideComments: hideCommentsState } = this.state;

    const activity = [...(comments || [])];

    activity.sort((item1, item2) => {
      return item2.createdAt - item1.createdAt;
    });

    return (
      <Container className="activity-block pb-3">
        <Row>
          {showCommentForm && (
            <Col>
              <Button
                className={`btn btn-sm btn-${
                  showCreateForm ? "danger" : "info"
                } text-capitalize  ${!hideComments && "float-end"}`}
                onClick={() =>
                  this.setState({ showCreateForm: !showCreateForm })
                }
              >
                <i className={`fa fa-${showCreateForm ? "close" : "plus"}`} />
                &nbsp;
                {showCreateForm
                  ? Translator.trans("activity.comment.close")
                  : Translator.trans("activity.comment.add")}
              </Button>
            </Col>
          )}
        </Row>
        {showCreateForm && showCommentForm && (
          <Row>
            {creationIsPending && (
              <Loader childStyle={{ height: "30px", paddingTop: 10 }} />
            )}
            <Col>
              <Form
                className="form-vertical"
                onSubmit={handleSubmit((values: any) => {
                  this.handleSubmit(resource, values);
                })}
              >
                {!creationIsPending && (
                  <>
                    <GenericFormComponent
                      type="Field"
                      name="comment"
                      required
                      label={Translator.trans("activity.comment.comment")}
                      isTextArea
                    />
                    <div>
                      <Button type="submit" className="btn btn-info btn">
                        {Translator.trans("activity.comment.submit")}
                      </Button>
                    </div>
                  </>
                )}
              </Form>
            </Col>
          </Row>
        )}
        <hr />
        {!activityResultFetched && <Loader />}
        {activityResultFetched && (
          <ActivityListCustomerServiceRecords
            items={activity}
            hideComments={
              hideCommentsState !== null ? hideCommentsState : hideComments
            }
          />
        )}
      </Container>
    );
  }
}

const formConfiguration = {
  form: "comment_form",
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { resource, hideComments, showCommentForm } = props;
  const comments = getCommentsMapping(state, resource);
  const creationIsPending = isCreationPending(state, resource);
  const activityResultFetched = isActivityResultFetched(state, resource);

  return {
    resource,
    hideComments: hideComments !== undefined,
    showCommentForm: showCommentForm !== undefined,
    comments,
    creationIsPending,
    activityResultFetched,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    getComments: (resource: any) => dispatch(getCommentsAction(resource)),
    createComment: (resource: any, message: any, file: any) =>
      dispatch(createCommentAction(resource, message, file)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(
  reduxForm<IFormData, IProps>(formConfiguration)(
    ActivityCustomerServiceRecords
  )
);
