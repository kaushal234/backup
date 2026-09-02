import React, { Fragment, useState } from "react";
import Translator from "bazinga-translator";
import ToggleButton from "react-bootstrap/ToggleButton";
import ToggleButtonGroup from "react-bootstrap/ToggleButtonGroup";
import { ActivityItemCustomerServiceRecords } from "./ActivityItemCustomerServiceRecords";
import { ActivityItemCustomerServiceRecordsSmallScreen } from "./ActivityItemCustomerServiceRecordsSmallScreen";

interface IProps {
  items: any;
  hideComments: any;
}

function ActivityListCustomerServiceRecords({
  items,
  hideComments = true,
}: IProps) {
  const discriminators = Array.from(
    new Set(items.map((item: any) => item.discriminator))
  );

  const [value, setValue] = useState(discriminators);
  const screenSize = window.innerWidth >= 992;
  const handleChange = (val: any) => setValue(val);
  return (
    <div>
      <ToggleButtonGroup
        className="pb-3"
        type="checkbox"
        value={value}
        onChange={handleChange}
      >
        {discriminators.map((discriminator: any, key) => {
          const isChecked = value.includes(discriminator);
          return (
            <ToggleButton
              variant="outline-info"
              className={`custom-outline-info ${
                isChecked
                  ? "custom-outline-info-checked"
                  : "custom-outline-info-unchecked"
              }`}
              key={key}
              id={`tbg-btn-${key}`}
              value={discriminator}
            >
              {discriminator}
            </ToggleButton>
          );
        })}
      </ToggleButtonGroup>
      {items.length === 0 && <p>{Translator.trans("activity.no_entry")}</p>}
      {items
        .filter((item: any) => {
          return value.includes(item.discriminator);
        })
        .filter((item: any) => {
          return hideComments !== true && item["@type"] === "Comment";
        })
        .map((item: any) => {
          return (
            <Fragment key={item["@id"] ? item["@id"] : item.id}>
              {!screenSize ? (
                <ActivityItemCustomerServiceRecordsSmallScreen
                  item={item}
                  key={item["@id"] ? item["@id"] : item.id}
                />
              ) : (
                <ActivityItemCustomerServiceRecords
                  item={item}
                  key={item["@id"] ? item["@id"] : item.id}
                />
              )}
            </Fragment>
          );
        })}
    </div>
  );
}

export default ActivityListCustomerServiceRecords;
