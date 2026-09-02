import React from "react";
import Translator from "bazinga-translator";
import Col from "react-bootstrap/Col";

interface IProps {
  item: any;
  showLogs: any;
}

export function ActivityItemUserStory({ item, showLogs }: IProps) {
  const itemId = item["@id"].split("/").pop();

  return !showLogs ? (
    <Col className="border border-1 rounded-1 me-1 my-1">
      <h5>
        {item.user === null
          ? item.metadata.length === 0
            ? "Anonymous"
            : `${item.metadata.lastname} ${item.metadata.firstname}`
          : `${item.user.lastname} ${item.user.firstname}`}
      </h5>
      <p className="small">{item.createdAt.format("yyyy-MM-d - HH:mm:ss")}</p>
      <p>
        {item["@type"] === "Comment" &&
          item.message.split("\n").map((element: any, key: number) => {
            return (
              <span key={key}>
                {element}
                {item.files.length !== 0 && (
                  <a
                    target="_blank"
                    className="btn btn-info btn-sm"
                    href={`/en/private/comments/${itemId}/files/${item.files[0].id}`}
                    rel="noreferrer"
                  >
                    <i className="fa fa-download">&nbsp;</i>
                    {Translator.trans("menu.download_file")}
                  </a>
                )}
              </span>
            );
          })}
      </p>
      {item.public === false && (
        <span className="label label-danger">
          {Translator.trans("activity.internal")}
        </span>
      )}
    </Col>
  ) : (
    <Col className="border border-1 rounded-1 me-1 my-1">
      <h5>{item.from}</h5>
      <p className="small">{item.createdAt.format("yyyy-MM-d - HH:mm:ss")}</p>
      {item.public === false && (
        <span className="label label-danger">
          {Translator.trans("activity.internal")}
        </span>
      )}
      {item["@type"] === "Log" &&
        Object.keys(item.changeSet).map((property, index) => {
          const changeSet = item.changeSet[property];
          changeSet.forEach((value: any, i: number) => {
            if (value === true) {
              changeSet[i] = Translator.trans("activity.log.yes");
            }
            if (value === false) {
              changeSet[i] = Translator.trans("activity.log.no");
            }
          });
          const empty = Translator.trans("activity.log.empty");
          return (
            <span key={index}>
              <div
                // eslint-disable-next-line react/no-danger
                dangerouslySetInnerHTML={{
                  __html: Translator.trans("activity.log.log_line", {
                    field: property,
                    oldValue: changeSet[0] || empty,
                    newValue: changeSet[1] || empty,
                  }),
                }}
              />
            </span>
          );
        })}
    </Col>
  );
}
