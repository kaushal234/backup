import React from "react";
import Translator from "bazinga-translator";
import sanitize from "sanitize-html";

interface IProps {
  item: any;
}

export function ActivityItem({ item }: IProps) {
  const itemId = (item["@id"] || "").split("/").pop();
  return (
    <div className="row m-b-md">
      <div className="col-sm-6 col-md-4 col-lg-3">
        <b>
          {item.user === null
            ? item.metadata.length === 0
              ? "Anonymous"
              : `${item.metadata.lastname} ${item.metadata.firstname}`
            : `${item.user.lastname} ${item.user.firstname}`}
        </b>
        <br />
        {item.createdAt.format("MMM Do YYYY, h:mm a")}
        <br />
        <small>
          <i>
            {Translator.trans("activity.type.name")}:{" "}
            {Translator.trans(`activity.type.${item["@type"]}`)}
          </i>
        </small>
        <br />
        {item.public === false && (
          <span className="label label-danger">
            {Translator.trans("activity.internal")}
          </span>
        )}
      </div>
      <div className="col-sm-6 col-md-8 col-lg-9 back-to-line">
        {item["@type"] === "Comment" && (
          <div
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={{ __html: sanitize(item.message) }}
            style={{ whiteSpace: "pre-wrap" }}
          />
        )}
        {item["@type"] === "Comment" && item.files?.length > 0 && (
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
        {item.metadata?.recipients && item.metadata?.recipients?.length > 0 && (
          <div>
            {Translator.trans("activity.log.recipients")} :{" "}
            {item.metadata.recipients.map((recipient: any, index: number) => (
              <span key={index}>
                {index > 0 && ", "}
                {recipient}
              </span>
            ))}
          </div>
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
                <br />
              </span>
            );
          })}
      </div>
    </div>
  );
}
