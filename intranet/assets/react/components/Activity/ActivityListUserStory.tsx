import React from "react";
import Translator from "bazinga-translator";
import Row from "react-bootstrap/Row";
import Loader from "../Loader";
import { ActivityItemUserStory } from "./ActivityItemUserStory";

interface IProps {
  items: any;
  showLogs: any;
  showComments: any;
}

function ActivityListUserStory({ items, showLogs, showComments }: IProps) {
  return (
    <Row className="flex flex-column mx-1">
      {items === null && <Loader />}
      {items !== null && items.length === 0 && (
        <p>{Translator.trans("activity.no_entry")}</p>
      )}
      {items !== null &&
        items
          .filter((item: any) => {
            return (
              (showLogs && item["@type"] === "Log") ||
              (showComments && item["@type"] === "Comment")
            );
          })
          .map((item: any) => {
            return (
              <ActivityItemUserStory
                item={item}
                key={item["@id"]}
                showLogs={showLogs}
              />
            );
          })}
    </Row>
  );
}

export default ActivityListUserStory;
