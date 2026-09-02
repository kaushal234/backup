import React, { Fragment, useState } from "react";
import Translator from "bazinga-translator";
import { ToggleButton, ToggleButtonGroup } from "@mui/material";
import { ActivityItemTechnicianOnCalls } from "../ActivityItemTechnicianOnCalls/ActivityItemTechnicianOnCalls";
import "./ActivityListTechnicianOnCalls.css";

interface IProps {
  items: any;
}

const MODULES = ["TOC", "CSR", "WC", "PDC"];

function ActivityListTechnicianOnCalls({ items }: IProps) {
  const [modules, setModules] = useState(MODULES);

  return (
    <div>
      <div className="activity_list_technician_on_calls__module_selector">
        <ToggleButtonGroup
          color="primary"
          value={modules}
          onChange={(event, value) => setModules(value)}
        >
          {MODULES.map((module) => (
            <ToggleButton key={module} value={module}>
              {module}
            </ToggleButton>
          ))}
        </ToggleButtonGroup>
      </div>
      {items.length === 0 && <p>{Translator.trans("activity.no_entry")}</p>}
      {items
        .filter((item: any) => {
          return item["@type"] === "Comment";
        })
        .filter((item: any) => modules.includes(item.discriminator))
        .map((item: any) => {
          return (
            <Fragment key={Math.random()}>
              <ActivityItemTechnicianOnCalls
                item={item}
                key={item["@id"] ? item["@id"] : item.id}
              />
            </Fragment>
          );
        })}
    </div>
  );
}

export default ActivityListTechnicianOnCalls;
