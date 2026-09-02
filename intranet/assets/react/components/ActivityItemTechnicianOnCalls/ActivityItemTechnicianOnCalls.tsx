import React from "react";
import {
  Container,
  Row,
  Col,
  Card,
  OverlayTrigger,
  Tooltip,
} from "react-bootstrap";
import { formatDateTimeLong } from "../../utils/date";
import { ActivityItemTechnicianOnCallsBadges } from "./ActivityItemTechnicianOnCallsBadges";
import "./ActivityItemTechnicianOnCalls.css";
import { IFile } from "../../types/IFile";
import FileChip from "../FileChip/FileChip";

interface IProps {
  item: any;
}

export function ActivityItemTechnicianOnCalls({ item }: IProps) {
  const date = new Date(item.createdAt);
  const translation = item.metadata?.translation ?? null;
  return (
    <Container>
      <Row>
        <Col className="d-inline-flex justify-content-between">
          <Row className="w-100">
            <Col xs={1}>
              <Card.Body>
                <div className="fs-6 fw-bold">#{item.position}</div>
              </Card.Body>
            </Col>

            <Col xs={11}>
              <Card className="ms-2 mb-2 rounded-3 position-relative">
                <Card.Body className="pt-2 pb-0">
                  <div className="activity_item_technician_on_calls__title_wrapper">
                    <div>
                      <div className="fs-6 fw-bold text-capitalize d-flex justify-between">
                        <div>
                          {item.user
                            ? `${item.user.firstname} ${item.user.lastname}`
                            : "Anonymous"}
                        </div>
                      </div>
                      <div className="fw-lighter">
                        {formatDateTimeLong(date.toString())}
                      </div>
                    </div>
                    <div className="activity_item_technician_on_calls__actions">
                      <div className="activity_item_technician_on_calls__badges">
                        <ActivityItemTechnicianOnCallsBadges item={item} />
                      </div>
                      {/* Only display this button if a translation exists */}
                      {translation && (
                        <OverlayTrigger
                          placement="top"
                          overlay={
                            <Tooltip>
                              Click here to see the original text
                            </Tooltip>
                          }
                        >
                          <span
                            role="button"
                            data-bs-toggle="collapse"
                            data-bs-target={`#collapse-comment-translation-${item.id}`}
                            aria-expanded="false"
                            aria-controls={`collapse-comment-translation-${item.id}`}
                          >
                            <i className="fa fa-language fa-xl" />
                          </span>
                        </OverlayTrigger>
                      )}
                    </div>
                  </div>
                </Card.Body>

                {item["@type"] === "Comment" && (
                  <Card.Body style={{ whiteSpace: "pre" }}>
                    {/* If no translation in the metadata, just display the message */}
                    {!translation && (
                      <Col sm={12} key={`${item.message}-${Math.random()}`}>
                        <div
                          className="text-wrap activity_item_technician_on_calls__pre_wrap"
                          // eslint-disable-next-line react/no-danger
                          dangerouslySetInnerHTML={{ __html: item.message }}
                        />
                      </Col>
                    )}

                    {/* If a translation is in the metadata, display the translation and the original text in a collapse */}
                    {translation && (
                      <>
                        <Col
                          sm={12}
                          key={`translated-${translation}-${Math.random()}`}
                        >
                          <div
                            className="text-wrap activity_item_technician_on_calls__pre_wrap"
                            // eslint-disable-next-line react/no-danger
                            dangerouslySetInnerHTML={{ __html: translation }}
                          />
                        </Col>

                        <div
                          id={`collapse-comment-translation-${item.id}`}
                          className="collapse mt-2 text-wrap"
                        >
                          <Col
                            sm={12}
                            key={`original-${item.message}-${Math.random()}`}
                          >
                            <Card>
                              <Card.Body
                                className="p-2 bg-light text-dark border-dark activity_item_technician_on_calls__pre_wrap"
                                dangerouslySetInnerHTML={{
                                  __html: item.message,
                                }}
                              />
                            </Card>
                          </Col>
                        </div>
                      </>
                    )}
                  </Card.Body>
                )}
                <Card.Body className="pb-3 pt-0 d-flex flex-wrap gap-2">
                  {(item?.files ?? []).map((file: IFile) => (
                    <FileChip
                      file={{
                        ...file,
                        filePath: `/en/private/uploads/${file.filePath}`,
                      }}
                    />
                  ))}
                </Card.Body>
              </Card>
            </Col>
          </Row>
        </Col>
      </Row>
    </Container>
  );
}
