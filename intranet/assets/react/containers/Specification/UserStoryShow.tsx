import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import { Container, Row, Col, Button } from "react-bootstrap";
import Swal from "sweetalert2";
import Translator from "bazinga-translator";
import {
  deleteUserStory,
  duplicateUserStory,
  getSpecification as getSpecificationAction,
  getUserStory as getUserStoryAction,
  getUserStoryReset as getUserStoryResetAction,
  updateStatusUserStory,
} from "../../actions/userStory/userStoryRequestAction";
import UserStoryDetail from "../../components/Specification/userStoryDetail/UserStoryDetail";
import UserStoryForm from "./UserStoryForm";
import UserStoryTable from "../../components/Specification/UserStoryTable";
import {
  specificationButtonColorSelector,
  userStoriesSelector,
  userStoryNextStatusSelector,
  userStorySelector,
} from "../../selectors/specification/userStoriesSelectors";
import TroubleTicketsTable from "../../components/MIS/TroubleTicketsTable";
import {
  addTroubleTicketToUserStories,
  getTroubleTicketsByModule as getTroubleTicketsByModuleAction,
  getTroubleTicketsReset as getTroubleTicketsResetAction,
} from "../../actions/mis/troubleTicketsActions";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  moduleId: any;
  moduleName: any;
  specificationId: any;
  getSpecification: any;
  getUserStory: any;
  getUserStoryReset: any;
  userStory: any;
  userStories: any;
  userStoryNextStatus: any;
  showUserStoryDetail: any;
  getDeleteUserStory: any;
  specificationStatus: any;
  statusSpecificationColor: any;
  getUpdateStatusUserStory: any;
  moo: any;
  mku: any;
  lku: any;
  mis: any;
  userStoryRefresh: any;
  showLoading: any;
  isLoading: any;
  getTroubleTicketsByModule: any;
  troubleTicketsByModule: any;
  showFailed: any;
  getDuplicateUserStory: any;
  getAddTroubleTicketToUserStories: any;
  getTroubleTicketsReset: any;
  troubleTicketsRefresh: any;
  loadingUserStory: any;
}

function UserStoryShow({
  moduleId,
  moduleName,
  specificationId,
  getSpecification,
  getUserStory,
  getUserStoryReset,
  userStory,
  userStories,
  userStoryNextStatus,
  showUserStoryDetail,
  getDeleteUserStory,
  specificationStatus,
  statusSpecificationColor,
  getUpdateStatusUserStory,
  moo,
  mku,
  lku,
  mis,
  userStoryRefresh,
  showLoading,
  isLoading,
  getTroubleTicketsByModule,
  troubleTicketsByModule,
  showFailed,
  getDuplicateUserStory,
  getAddTroubleTicketToUserStories,
  getTroubleTicketsReset,
  troubleTicketsRefresh,
  loadingUserStory,
}: IProps) {
  const [selectedRow, setSelectedRow] = useState(false);
  const [showList, setShowList] = useState(true);
  const [showCreationForm, setShowCreationForm] = useState(false);
  const [showEditForm, setShowEditForm] = useState(false);

  useEffect(() => {
    getSpecification(specificationId);
    getUserStoryReset();
  }, [userStoryRefresh]);

  useEffect(() => {
    getTroubleTicketsByModule(moduleId);
    getTroubleTicketsReset();
  }, [troubleTicketsRefresh]);

  const handleShowDetail = (userStoryId: any) => {
    getUserStory(userStoryId);
    setSelectedRow(userStoryId);
  };

  const handleAddUserStory = () => {
    setShowList(false);
    setShowCreationForm(true);
    setSelectedRow(false);
  };
  const handleEditUserStory = () => {
    setShowList(false);
    setShowEditForm(true);
    if (userStory.status === "VALIDATED") {
      getUpdateStatusUserStory(userStory.id, "EDIT");
    }
  };
  const handleCloseForm = () => {
    if (!showFailed) {
      setShowCreationForm(false);
      setShowEditForm(false);
      setShowList(true);
    }
  };

  const handleDeleteUserStory = () => {
    Swal.fire({
      icon: "warning",
      text: "Are you sure you want to delete this user?",
      title: "Delete",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "YES",
      cancelButtonText: "NO",
    }).then((result) => {
      if (result.isConfirmed) {
        getDeleteUserStory(userStory.id);
      }
    });
  };

  const handleUpdateStatusUserStory = () => {
    getUpdateStatusUserStory(userStory.id, userStory.status);
    getUserStory(userStory.id);
  };

  const handleDuplicateUSerStory = () => {
    getDuplicateUserStory(userStory.id);
  };

  const handleAddTTSToList = (troubleTicketId: any) => {
    getAddTroubleTicketToUserStories(troubleTicketId);
  };

  if (showLoading)
    return (
      <div className="spinner-border" role="status">
        <span className="sr-only">
          {Translator.trans("mis.specification.loading")}
        </span>
      </div>
    );

  return (
    <Container fluid>
      {showList && (
        <>
          <Row>
            <Col className="d-flex gap-3 justify-content-center align-items-center">
              <a
                href={`/en/private/mis/modules/${moduleId}/specifications/${specificationId}/downloadXLSX`}
              >
                <Button variant="danger" className="mt-1 mb-2 p-1 pe-2">
                  <i className="fa fa-download" />
                  &nbsp; {Translator.trans("mis.specification.download")}
                </Button>
              </a>
              <Button
                variant="info"
                className="mt-1 mb-2 p-1 pe-2"
                onClick={() => handleAddUserStory()}
              >
                <i className="fa fa-plus" />
                &nbsp; {Translator.trans("mis.specification.add_user_story")}
              </Button>
            </Col>
          </Row>
          <Row>
            <Col sm={8} className="p-0">
              <Row>
                <Col xs={6} xxl={4}>
                  <div className="widget bg-body">
                    <div className="row">
                      <div className=" col-4 p-0">
                        <i className="fa fa-suitcase fa-3x" />
                      </div>
                      <div className="col-8 p-0 text-end">
                        <span>
                          {Translator.trans("mis.specification.module_name")}
                        </span>
                        <h3 className="font-bold">{moduleName}</h3>
                      </div>
                    </div>
                  </div>
                </Col>
                <Col xs={6} xxl={4}>
                  <div
                    className="widget"
                    style={{
                      backgroundColor: statusSpecificationColor,
                      color: "#FFF",
                    }}
                  >
                    <div className="row">
                      <div className="col-4">
                        {specificationStatus === "DEVELOPMENT" ? (
                          <i className="fa fa-gear fa-3x" />
                        ) : (
                          <i className="fa fa-check-square fa-3x" />
                        )}
                      </div>
                      <div className="col-8 text-end">
                        <span>
                          {Translator.trans("mis.specification.status")}
                        </span>
                        <h3 className="font-bold">{specificationStatus}</h3>
                      </div>
                    </div>
                  </div>
                </Col>
              </Row>
              <Row>
                <Col className="overflow-scroll" style={{ maxHeight: "65vh" }}>
                  <UserStoryTable
                    userStories={userStories}
                    onShowDetail={handleShowDetail}
                    selectedRow={selectedRow}
                  />
                </Col>
              </Row>
              <Row className="pt-2">
                <Col className="overflow-scroll" style={{ maxHeight: "65vh" }}>
                  {!isLoading && (
                    <TroubleTicketsTable
                      troubleTicketsByModule={troubleTicketsByModule}
                      onTSSAddToList={handleAddTTSToList}
                    />
                  )}
                </Col>
              </Row>
            </Col>
            <Col
              sm={4}
              className="overflow-scroll"
              style={{ maxHeight: "80vh" }}
            >
              {!loadingUserStory ? (
                showUserStoryDetail && (
                  <UserStoryDetail
                    moo={moo}
                    mku={mku}
                    lku={lku}
                    mis={mis}
                    moduleId={moduleId}
                    specificationId={specificationId}
                    userStory={userStory}
                    userStoryNextStatus={userStoryNextStatus}
                    onShowEdit={handleEditUserStory}
                    onDeleteUserStory={handleDeleteUserStory}
                    onUpdateStatusUserStory={handleUpdateStatusUserStory}
                    onDuplicateUserStory={handleDuplicateUSerStory}
                  />
                )
              ) : (
                <div className="spinner-border" role="status">
                  <span className="sr-only">
                    {Translator.trans("mis.specification.loading")}
                  </span>
                </div>
              )}
            </Col>
          </Row>
        </>
      )}
      {showCreationForm && (
        <UserStoryForm
          formType="creation"
          specificationId={specificationId}
          moduleName={moduleName}
          onCloseForm={handleCloseForm}
        />
      )}
      {showEditForm && (
        <UserStoryForm
          formType="edit"
          userStoryValues={userStory}
          moduleName={moduleName}
          onCloseForm={handleCloseForm}
        />
      )}
    </Container>
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    userStory: userStorySelector(state),
    userStoryNextStatus: userStoryNextStatusSelector(state),
    statusSpecificationColor: specificationButtonColorSelector(state),
    showUserStoryDetail: state.specification.showUserStoryDetail,
    showLoading: state.specification.showLoading,
    loadUserStory: state.specification.loadUserStory,
    userStories: userStoriesSelector(state),
    specificationStatus: state.specification.specificationStatus,
    userStoryRefresh: state.specification.userStoryRefresh,
    troubleTicketsRefresh: state.mis.troubleTicketsRefresh,
    troubleTicketsByModule: state.mis.troubleTicketsByModule,
    isLoading: state.mis.isLoading,
    showFailed: state.specification.showFailed,
    loadingUserStory: state.specification.loadingUserStory,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    getUserStory: (userStoryId: any) =>
      dispatch(getUserStoryAction(userStoryId)),
    getDeleteUserStory: (userStoryId: any) =>
      dispatch(deleteUserStory(userStoryId)),
    getUpdateStatusUserStory: (userStoryId: any, userStoryStatus: any) =>
      dispatch(updateStatusUserStory(userStoryId, userStoryStatus)),
    getSpecification: (SpecificationId: any) =>
      dispatch(getSpecificationAction(SpecificationId)),
    getUserStoryReset: () => dispatch(getUserStoryResetAction()),
    getTroubleTicketsByModule: (moduleId: any) =>
      dispatch(getTroubleTicketsByModuleAction(moduleId)),
    getDuplicateUserStory: (userStoryId: any) =>
      dispatch(duplicateUserStory(userStoryId)),
    getTroubleTicketsReset: () => dispatch(getTroubleTicketsResetAction()),
    getAddTroubleTicketToUserStories: (TTSId: any) =>
      dispatch(addTroubleTicketToUserStories(TTSId)),
  };
};
export default connect(mapStateToProps, mapDispatchToProps)(UserStoryShow);
