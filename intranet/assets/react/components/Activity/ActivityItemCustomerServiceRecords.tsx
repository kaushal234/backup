import React from "react";
import { Container, Row, Col } from "react-bootstrap";
import { formatDateTimeLong } from "../../utils/date";

interface IProps {
  item: any;
}

export function ActivityItemCustomerServiceRecords({ item }: IProps) {
  const date = new Date(item.createdAt);

  return (
    <Container className="tracking-list bg-body">
      <Row className="tracking-item pe-0">
        <Col className="tracking-date">
          <p className="m-0">
            <strong>{item.discriminator}</strong>
            <br />
            {formatDateTimeLong(date.toString())}
          </p>
        </Col>
        <Col className="tracking-icon status-intransit hover-text">
          <span className="tooltip-text customer-service-records" id="top">
            {item.user
              ? `${item.user.firstname} ${item.user.lastname}`
              : item.firstname && item.lastname
              ? `${item.firstname} ${item.lastname}`
              : "Anonymous"}
          </span>
          <svg
            className="svg-inline--fa fa-circle fa-w-16"
            aria-hidden="true"
            data-prefix="fas"
            data-icon="circle"
            role="img"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 512 512"
            data-fa-i2svg=""
          >
            <path
              fill="currentColor"
              d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"
            />
            <text x="50%" y="50%" textAnchor="middle" dy=".3em" fontSize="200">
              {item.user
                ? `${item.user.firstname.slice(0, 1)}${item.user.lastname.slice(
                    0,
                    1
                  )}`
                : item.firstname && item.lastname
                ? `${item.firstname.slice(0, 1)}${item.lastname.slice(0, 1)}`
                : ""}
            </text>
          </svg>
        </Col>
        {item["@type"] === "Comment" &&
          item.message.split("\n").map((element: any, key: number) => {
            const validDiscriminators = [
              "TOC",
              "SBL",
              "CSR",
              "NCR",
              "BP",
              "CRAB",
            ];
            return (
              <Col sm={12} key={key}>
                {validDiscriminators.includes(item.discriminator) ? (
                  <div
                    className="tracking-content"
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={{ __html: element }}
                  />
                ) : (
                  <p className="tracking-content">{element}</p>
                )}
              </Col>
            );
          })}
      </Row>
    </Container>
  );
}
