import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { InjectedFormProps, reduxForm } from "redux-form";
import { getLogs as getLogsAction } from "../../actions/activity/logsActions";
import {
  createComment as createCommentAction,
  getComments as getCommentsAction,
} from "../../actions/activity/commentsActions";
import {
  getCommentsMapping,
  getLogsMapping,
  isActivityResultFetched,
  isCreationPending,
} from "../../selectors/activity/activitySelector";
import ActivityList from "../../components/Activity/ActivityList";
import Loader from "../../components/Loader";
import { AppDispatch, RootState } from "../../store";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";

type IFormData = any;

interface IProps {
  createComment?: any;
  resource: any;
  hideComments?: any;
  showCommentForm?: any;
  hideLogs?: any;
  getComments?: any;
  getLogs?: any;
  comments?: any;
  logs?: any;
  creationIsPending?: any;
  activityResultFetched?: any;
  hasFileUpload?: any;
}

interface IMappedProps {
  fileUpload?: any;
}

interface IState {
  showCreateForm: any;
  hideLogs: any;
  hideComments: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class Activity extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      hideComments: null,
      hideLogs: null,
      showCreateForm: false,
    };
  }

  componentDidUpdate(prevProps: IWrappedProps) {
    const { creationIsPending } = this.props;
    if (creationIsPending === false && prevProps.creationIsPending === true) {
      this.setState({ showCreateForm: false, hideComments: false });
    }
  }

  handleSubmit(resource: any, data: any) {
    const { createComment } = this.props;
    let file = null;
    if (data.file && data.file[0]) {
      [file] = data.file;
    }
    createComment(resource, data.comment, file);

    // Clear form
    data.comment = "";
    data.file = "";
  }

  render() {
    const {
      resource,
      hideComments,
      showCommentForm,
      hideLogs,
      getComments,
      getLogs,
      comments,
      logs,
      handleSubmit,
      creationIsPending,
      activityResultFetched,
      hasFileUpload,
    } = this.props;

    const {
      showCreateForm,
      hideLogs: hideLogsState,
      hideComments: hideCommentsState,
    } = this.state;

    if (!hideComments && comments === null) {
      getComments(resource);
    }

    if (!hideLogs && logs === null) {
      getLogs(resource);
    }

    const activity = [...(comments || []), ...(logs || [])];

    activity.sort((item1, item2) => {
      return item2.createdAt - item1.createdAt;
    });

    return (
      <div className="activity-block">
        <div className="row">
          {!hideComments && !hideLogs && (
            <div className="col-md-6">
              <div className="btn-group" role="group">
                <button
                  type="button"
                  className={`btn btn-sm ${
                    hideLogsState || hideCommentsState
                      ? "btn-danger"
                      : "btn-green"
                  }`}
                  onClick={() => {
                    this.setState({ hideLogs: false, hideComments: false });
                  }}
                >
                  <i
                    className={`fa ${
                      hideLogsState || hideCommentsState
                        ? "fa-times"
                        : "fa-check"
                    }`}
                  />
                  &nbsp;{Translator.trans("activity.all")}
                </button>
                <button
                  type="button"
                  className={`btn btn-sm ${
                    hideCommentsState ? "btn-danger" : "btn-green"
                  }`}
                  onClick={() => {
                    this.setState({ hideComments: false, hideLogs: true });
                  }}
                >
                  <i
                    className={`fa ${
                      hideCommentsState ? "fa-times" : "fa-check"
                    }`}
                  />
                  &nbsp;{Translator.trans("activity.comment.name")}
                </button>
                <button
                  type="button"
                  className={`btn btn-sm ${
                    hideLogsState ? "btn-danger" : "btn-green"
                  }`}
                  onClick={() => {
                    this.setState({ hideLogs: false, hideComments: true });
                  }}
                >
                  <i
                    className={`fa ${hideLogsState ? "fa-times" : "fa-check"}`}
                  />
                  &nbsp;{Translator.trans("activity.log.name")}
                </button>
              </div>
            </div>
          )}
          {showCommentForm && (
            <div className="col-md-6">
              <button
                type="button"
                className={`btn btn-sm btn-${
                  showCreateForm ? "danger" : "info"
                } text-capitalize  ${
                  !hideComments && !hideLogs && "float-end"
                }`}
                onClick={() =>
                  this.setState({
                    showCreateForm: !this.state.showCreateForm,
                  })
                }
              >
                <i className={`fa fa-${showCreateForm ? "close" : "plus"}`} />
                &nbsp;
                {showCreateForm
                  ? Translator.trans("activity.comment.close")
                  : Translator.trans("activity.comment.add")}
              </button>
            </div>
          )}
        </div>
        {showCreateForm && showCommentForm && (
          <div className="row">
            {creationIsPending && (
              <Loader childStyle={{ height: "30px", paddingTop: 10 }} />
            )}
            <div className="col-md-6">
              <form
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
                    {hasFileUpload && (
                      <GenericFormComponent
                        type="FileInput"
                        name="file"
                        label={Translator.trans("activity.comment.file")}
                      />
                    )}
                    <div>
                      <button type="submit" className="btn btn-info btn">
                        {Translator.trans("activity.comment.submit")}
                      </button>
                    </div>
                  </>
                )}
              </form>
            </div>
          </div>
        )}
        <hr />
        {!activityResultFetched && <Loader />}
        {activityResultFetched && (
          <ActivityList
            items={activity}
            hideComments={
              hideCommentsState !== null ? hideCommentsState : hideComments
            }
            hideLogs={hideLogsState !== null ? hideLogsState : hideLogs}
          />
        )}
      </div>
    );
  }
}

const formConfiguration = {
  form: "comment_form",
};

const mapStateToProps = (state: RootState, props: IProps & IMappedProps) => {
  const { resource, hideComments, hideLogs, showCommentForm, fileUpload } =
    props;
  const logs = getLogsMapping(state, resource);
  const comments = getCommentsMapping(state, resource);
  const creationIsPending = isCreationPending(state, resource);
  const activityResultFetched = isActivityResultFetched(state, resource);

  return {
    resource,
    hideComments: hideComments !== undefined,
    hideLogs: hideLogs !== undefined,
    showCommentForm: showCommentForm !== undefined,
    hasFileUpload: Boolean(fileUpload),
    logs,
    comments,
    creationIsPending,
    activityResultFetched,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    getLogs: (resource: any) => dispatch(getLogsAction(resource)),
    getComments: (resource: any) => dispatch(getCommentsAction(resource)),
    createComment: (resource: any, message: any, file: any) => {
      return dispatch(createCommentAction(resource, message, file));
    },
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(Activity));
