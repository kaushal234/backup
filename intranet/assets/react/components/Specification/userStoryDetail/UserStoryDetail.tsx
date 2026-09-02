import React from "react";
import { Button, Card } from "react-bootstrap";
import Translator from "bazinga-translator";
import DOMPurify from "dompurify";
import UserStoryButton from "./UserStoryButton";
import Group from "./Group";
import ActivityUserStory from "../../../containers/Activity/ActivityUserStory";

interface IProps {
  moo: any;
  mku: any;
  lku: any;
  mis: any;
  moduleId: any;
  specificationId: any;
  onShowEdit: any;
  userStory: any;
  userStoryNextStatus: any;
  onDeleteUserStory: any;
  onUpdateStatusUserStory: any;
  onDuplicateUserStory: any;
}

function UserStoryDetail({
  moo,
  mku,
  lku,
  mis,
  moduleId,
  specificationId,
  onShowEdit,
  userStory,
  userStoryNextStatus,
  onDeleteUserStory,
  onUpdateStatusUserStory,
  onDuplicateUserStory,
}: IProps) {
  const sanitizeHTML = (html: any) => {
    return DOMPurify.sanitize(html);
  };

  return (
    <Card className="m-0 shadow">
      <Card.Header
        className="bg-white position-sticky p-0 m-0"
        style={{ zIndex: "99", top: "0" }}
      >
        <div className="d-flex flex-wrap gap-1 p-1">
          {(mis || moo || mku || lku) && (
            <Button
              variant="warning"
              className="p-0 ps-1 pe-1"
              onClick={() => onShowEdit()}
            >
              <i className="fa fa-pen" />
              &nbsp;{Translator.trans("mis.specification.button.edit")}
            </Button>
          )}
          {mis && (
            <UserStoryButton
              name={Translator.trans("mis.specification.button.jira")}
              icon="arrow-up"
              btnColor="info"
              route={`${moduleId}/specifications/${specificationId}/${userStory.id}/transfer-to-jira`}
            />
          )}
          <UserStoryButton
            name={Translator.trans("mis.specification.button.file")}
            icon="file"
            route={`${moduleId}/specifications/${userStory.id}/files`}
          />
          {(mis || moo || mku || lku) && (
            <Button
              variant="danger"
              className="p-0 ps-1 pe-1"
              onClick={() => onDeleteUserStory()}
            >
              <i className="fa fa-trash" />
              &nbsp;{Translator.trans("mis.specification.button.delete")}
            </Button>
          )}
          {mis && (
            <Button
              variant={userStoryNextStatus.color}
              className="p-0 ps-1 pe-1"
              onClick={
                userStory.status !== "VALIDATED"
                  ? () => onUpdateStatusUserStory()
                  : undefined
              }
              style={{
                cursor:
                  userStory.status !== "VALIDATED" ? "pointer" : "default",
              }}
            >
              {userStory.status !== "VALIDATED" && (
                <i className="fa fa-arrow-up" />
              )}
              &nbsp;{userStoryNextStatus.name}
            </Button>
          )}
          {(mis || moo || mku || lku) && (
            <Button
              variant="success"
              className="p-0 ps-1 pe-1"
              onClick={() => onDuplicateUserStory()}
            >
              <i className="fa-duotone fa-solid fa-copy" />
              &nbsp;{Translator.trans("mis.specification.button.duplicate")}
            </Button>
          )}
        </div>
      </Card.Header>
      <Card.Body className="p-1">
        <div className="d-flex flex-wrap gap-1">
          <h4 className="badge bg-info text-white">
            {Translator.trans("mis.specification.user_story_form.category")}
            :&nbsp;
            {userStory.category}
          </h4>
          <h4 className="fw-bold badge bg-info text-white">
            {Translator.trans("mis.specification.category.status")}:&nbsp;
            {userStory.status}
          </h4>
          <h4 className="fw-bold badge bg-info text-white">
            {Translator.trans("mis.specification.createdBy")}:&nbsp;
            {userStory.createdBy.firstname} {userStory.createdBy.lastname}
          </h4>
        </div>
        <p
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{
            __html: sanitizeHTML(userStory.description),
          }}
        />
        {userStory.peopleProperties &&
          userStory.peopleProperties.length > 0 && (
            <>
              <hr />
              <h4 className="fw-bold">
                {Translator.trans(
                  "mis.specification.user_story_form.people_property"
                )}
              </h4>
              <p className="ps-2">{userStory.peopleProperties.join(" / ")}</p>
              <hr />
            </>
          )}
        {userStory.roleAccesses && userStory.roleAccesses.length > 0 && (
          <>
            <Group
              title={Translator.trans(
                "mis.specification.tabs.roles.role_accesses"
              )}
              role={userStory.roleAccesses}
            />
            <hr />
          </>
        )}
        {userStory.currentEmail && (
          <div>
            <div className="d-flex justify-content-between">
              <h4 className="fw-bold">
                {Translator.trans("mis.specification.category.email")}
              </h4>
              {userStory.currentEmail.follower && (
                <div className="pe-5">
                  <label htmlFor="followerCheckbox">
                    {Translator.trans(
                      "mis.specification.user_story_form.follower"
                    )}
                    &nbsp;
                  </label>
                  <input
                    type="checkbox"
                    id="followerCheckbox"
                    disabled
                    checked={userStory.currentEmail.follower}
                  />
                </div>
              )}
            </div>
            <p className="ps-2">
              <span className="fw-bold">
                {Translator.trans(
                  "mis.specification.user_story_form.object_email"
                )}{" "}
                :
              </span>{" "}
              {userStory.currentEmail.object}
            </p>
            <p className="ps-2">
              <span className="fw-bold">
                {Translator.trans("mis.specification.user_story_form.email")} :
              </span>{" "}
              {userStory.currentEmail.body}
            </p>
            {userStory.currentEmail.peopleProperties &&
              userStory.currentEmail.peopleProperties.length > 0 && (
                <>
                  <hr />
                  <h4 className="fw-bold">
                    {Translator.trans(
                      "mis.specification.user_story_form.people_property"
                    )}
                  </h4>
                  <p className="ps-2">
                    {userStory.currentEmail.peopleProperties.join(" / ")}
                  </p>
                </>
              )}
            {userStory.currentEmail.recipientAccesses.length > 0 && (
              <Group
                title={Translator.trans(
                  "mis.specification.user_story_form.recipient_notify"
                )}
                role={userStory.currentEmail.recipientAccesses}
              />
            )}
            {userStory.currentEmail.copyAccesses.length > 0 && (
              <Group
                title={Translator.trans(
                  "mis.specification.user_story_form.copy_notify"
                )}
                role={userStory.currentEmail.copyAccesses}
              />
            )}
            <hr />
          </div>
        )}
        {userStory.currentNotification && (
          <div>
            <div className="d-flex justify-content-between">
              <h4 className="fw-bold">
                {Translator.trans("mis.specification.notification")}
              </h4>
              {userStory.currentNotification.follower && (
                <div className="pe-5">
                  <label htmlFor="followerCheckbox">
                    {Translator.trans(
                      "mis.specification.user_story_form.follower"
                    )}
                    &nbsp;
                  </label>
                  <input
                    type="checkbox"
                    id="followerCheckbox"
                    disabled
                    checked={userStory.currentNotification.follower}
                  />
                </div>
              )}
            </div>
            <p className="ps-2">
              <span className="fw-bold">
                {Translator.trans("mis.specification.message")} :
              </span>{" "}
              {userStory.currentNotification.message}
            </p>
            {userStory.currentNotification.roleToNotify.length > 0 && (
              <Group
                title={Translator.trans(
                  "mis.specification.user_story_form.role_to_notify"
                )}
                role={userStory.currentNotification.roleToNotify}
              />
            )}
          </div>
        )}
        <hr />
        <div className="section">
          <ActivityUserStory resource={userStory["@id"]} />
        </div>
      </Card.Body>
    </Card>
  );
}

export default UserStoryDetail;
