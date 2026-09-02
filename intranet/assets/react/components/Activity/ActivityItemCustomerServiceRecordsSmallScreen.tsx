import React from "react";
import { Col, Card } from "react-bootstrap";
import { formatDateTimeLong } from "../../utils/date";

interface IProps {
  item: any;
}

export function ActivityItemCustomerServiceRecordsSmallScreen({
  item,
}: IProps) {
  return (
    <Card className="w-100 mb-3">
      <Card.Body className="p-3">
        <div>
          <h5 className="pt-3 text-secondary">
            {item.user ? (
              <>
                {item.user.firstname} {item.user.lastname}{" "}
                <span className="text-primary">({item.discriminator})</span>
              </>
            ) : item.firstname && item.lastname ? (
              <>
                {item.firstname} {item.lastname}{" "}
                <span className="text-primary">({item.discriminator})</span>
              </>
            ) : (
              "Anonymous"
            )}
          </h5>
          <p className="small">{formatDateTimeLong(item.createdAt)}</p>
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
                      className="text-secondary comment-customer-service-record"
                      // eslint-disable-next-line react/no-danger
                      dangerouslySetInnerHTML={{ __html: element }}
                    />
                  ) : (
                    <p className="text-secondary comment-customer-service-record">
                      {element}
                    </p>
                  )}
                </Col>
              );
            })}
        </div>
      </Card.Body>
    </Card>
  );
}
