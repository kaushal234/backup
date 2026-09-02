import React from "react";
import Translator from "bazinga-translator";
import Loader from "../Loader";
import { ActivityItem } from "./ActivityItem";

interface IProps {
  items: any;
  hideLogs: any;
  hideComments: any;
}

function ActivityList({ items, hideLogs = true, hideComments = true }: IProps) {
  return (
    <div>
      {items === null && <Loader />}
      {items !== null && items.length === 0 && (
        <p>{Translator.trans("activity.no_entry")}</p>
      )}
      {items !== null &&
        items
          .filter((item: any) => {
            return (
              (hideLogs !== true && item["@type"] === "Log") ||
              (hideComments !== true && item["@type"] === "Comment")
            );
          })
          .map((item: any) => {
            return <ActivityItem item={item} key={Math.random()} />;
          })}
    </div>
  );
}

export default ActivityList;
