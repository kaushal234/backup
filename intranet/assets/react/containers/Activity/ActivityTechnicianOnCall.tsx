import React, { useEffect } from "react";
import Translator from "bazinga-translator";
import { reduxForm, change, InjectedFormProps, reset } from "redux-form";
import { Button, Col, Container, Form, Row, Collapse } from "react-bootstrap";
import { getComments } from "../../actions/activity/commentsActions";
import {
  getCommentsMapping,
  isActivityResultFetched,
  isCreationPending,
} from "../../selectors/activity/activitySelector";
import Loader from "../../components/Loader";
import ActivityListTechnicianOnCalls from "../../components/ActivityListTechnicianOnCalls/ActivityListTechnicianOnCalls";
import "./ActivityTechnicianOnCall.css";
import {
  ACCEPT_FILES,
  COMMENT_TYPES_RADIO_OPTIONS,
  COMMENT_TYPES,
  CONFIDENTIAL_COMMENT_TYPES_RADIO_OPTIONS,
  FACTORY_FLAG,
} from "../../constants/constants";
import { IActivityTechnicianOnCallFormData } from "../../types/IActivityTechnicianOnCallFormData";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";
import {
  IPostCommentByTocIdApiPayload,
  postCommentById,
} from "../../api/postCommentById";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";

const formName = "comment_form";
interface IProps {
  resource: string;
  confidential: boolean;
  status?: string;
  grantedFactoryFlag?: string;
}

type IWrappedProps = IProps &
  InjectedFormProps<IActivityTechnicianOnCallFormData, IProps>;

function ActivityTechnicianOnCall({
  resource,
  confidential,
  handleSubmit,
  status,
  grantedFactoryFlag,
}: IWrappedProps) {
  const dispatch = useAppDispatch();

  const tocData = useAppSelector((state) => state.tocDetail.data);

  const comments = useAppSelector((state) =>
    getCommentsMapping(state, resource)
  );
  const creationIsPending = useAppSelector((state) =>
    isCreationPending(state, resource)
  );
  const activityResultFetched = useAppSelector((state) =>
    isActivityResultFetched(state, resource)
  );

  const formValues: IActivityTechnicianOnCallFormData | undefined =
    useAppSelector((state) => state.form[formName]?.values);

  const commentType = formValues?.commentType;

  useEffect(() => {
    if (formValues?.changeFactoryFlagStatus) {
      dispatch(
        change(formName, "commentType", COMMENT_TYPES.internalWithNotification)
      );
    }
  }, [formValues?.changeFactoryFlagStatus]);

  useEffect(() => {
    if (commentType && commentType !== COMMENT_TYPES.internalWithNotification) {
      dispatch(change(formName, "changeFactoryFlagStatus", false));
    }
  }, [commentType, dispatch]);

  const handleFormSubmit = async (
    newResource: string,
    data: IActivityTechnicianOnCallFormData
  ) => {
    const newFactoryFlag = tocData?.factoryFlag
      ? FACTORY_FLAG.close
      : FACTORY_FLAG.open;
    const factoryFlagValue = data.changeFactoryFlagStatus
      ? newFactoryFlag
      : undefined;

    const params: IPostCommentByTocIdApiPayload = {
      "@id": newResource,
      comment: data.comment ?? "",
      public: factoryFlagValue
        ? false
        : data.commentType === COMMENT_TYPES.external,
      ...(data.file?.[0] && { file: data.file[0] }),
      metadata: {
        notifications:
          !!factoryFlagValue || data.commentType !== COMMENT_TYPES.internal,
        ...(factoryFlagValue && { factoryFlag: factoryFlagValue }),
      },
    };

    dispatch(showGlobalLoader(true));
    const response = await postCommentById(params);
    dispatch(showGlobalLoader(false));

    if (response.data) {
      dispatch(reset(formName));
      await dispatch(fetchTechnicianOnCallThunk({ tocId: `${tocData?.id}` }));
      dispatch(getComments(resource));
    }
  };

  useEffect(() => {
    if (comments === null) {
      dispatch(getComments(resource));
    }
  }, [dispatch, resource, comments]);

  const activity = [...(comments || [])].sort(
    (item1, item2) => item2.createdAt - item1.createdAt
  );

  return (
    <Container className="technician_on_call__wrapper">
      <Row>
        {creationIsPending && (
          <Loader childStyle={{ height: "30px", paddingTop: 10 }} />
        )}
        <Col>
          <Form
            onSubmit={handleSubmit((values) =>
              handleFormSubmit(resource, values)
            )}
          >
            {!creationIsPending && (
              <>
                <Collapse
                  in={commentType === COMMENT_TYPES.external}
                  mountOnEnter
                  unmountOnExit
                >
                  <div>
                    <div className="alert alert-danger mb-2" role="alert">
                      {Translator.trans("toc.messages.warning.comment")}
                    </div>
                  </div>
                </Collapse>
                <GenericFormComponent
                  type="RichTextField"
                  name="comment"
                  editorHeight="100px"
                  required
                  placeholder={Translator.trans("activity.comment.add")}
                />
                {!["SOLVED", "CLOSED"].includes(status ?? "") &&
                  grantedFactoryFlag === "1" && (
                    <Row>
                      <Col xs={7} md={8}>
                        <GenericFormComponent
                          type="Radio"
                          name="commentType"
                          list={
                            confidential
                              ? CONFIDENTIAL_COMMENT_TYPES_RADIO_OPTIONS
                              : COMMENT_TYPES_RADIO_OPTIONS
                          }
                          horizontalOptions
                        />
                      </Col>
                      <Col xs={5} md={4}>
                        <div className="activity_technician_on_call__switch_wrapper">
                          <GenericFormComponent
                            type="Switch"
                            label={
                              tocData?.factoryFlag
                                ? Translator.trans(
                                    "toc.fields.factory_flag_close"
                                  )
                                : Translator.trans(
                                    "toc.fields.factory_flag_open"
                                  )
                            }
                            name="changeFactoryFlagStatus"
                          />
                        </div>
                      </Col>
                    </Row>
                  )}
                <GenericFormComponent
                  type="FileInput"
                  label="Upload File"
                  name="file"
                  accept={ACCEPT_FILES.comment}
                />
                <p className="text-muted small mb-1">
                  PDF, Word, Excel, JPEG, PNG, ZIP, PPT, MP4, Outlook — max 25
                  MB
                </p>
                <Button type="submit" className="btn btn-info btn mt-2">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="30"
                    height="30"
                    fill="currentColor"
                    className="bi bi-send"
                    viewBox="0 0 16 16"
                  >
                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z" />
                  </svg>
                  <span className="ms-3">
                    {Translator.trans("activity.comment.submit")}
                  </span>
                </Button>
              </>
            )}
          </Form>
        </Col>
      </Row>
      <hr />
      {!activityResultFetched && <Loader />}
      {activityResultFetched && (
        <ActivityListTechnicianOnCalls items={activity} />
      )}
    </Container>
  );
}

const formConfiguration = {
  form: formName,
  initialValues: {
    commentType: COMMENT_TYPES.internalWithNotification,
    changeFactoryFlagStatus: false,
  },
};

export default reduxForm<IActivityTechnicianOnCallFormData, IProps>(
  formConfiguration
)(ActivityTechnicianOnCall);
