import Tabs from "@mui/material/Tabs";
import Tab from "@mui/material/Tab";
import Box from "@mui/material/Box";
import React, { ReactNode, useEffect, useState } from "react";
import "./TabWrapper.css";
import { useTranslation } from "react-i18next";
import { Badge } from "@mui/material";
import CustomTabPanel, { a11yProps } from "../CustomTabPanel/CustomTabPanel";

interface ITabWrapperProps {
  tabs: Array<{
    title: string | ReactNode;
    component: ReactNode;
    dataCy: string;
    badgeCount?: number;
  }>;
  overrideTab?: number;
}

function TabWrapper({ tabs, overrideTab }: ITabWrapperProps) {
  const { t } = useTranslation();
  const [value, setValue] = useState(0);

  const handleChange = (event: React.SyntheticEvent, newValue: number) => {
    setValue(newValue);
  };

  useEffect(() => {
    if (overrideTab) {
      setValue(overrideTab);
    }
  }, [overrideTab]);

  return (
    <div className="tab_wrapper__wrapper">
      <Box sx={{ borderBottom: 1, borderColor: "divider" }}>
        <Tabs
          centered
          variant="fullWidth"
          value={value}
          onChange={handleChange}
          aria-label="tabs"
        >
          {tabs.map((tab, idx) => {
            const title =
              typeof tab.title === "string" ? t(tab.title) : tab.title;

            return (
              <Tab
                key={tab.dataCy}
                label={
                  tab.badgeCount !== undefined ? (
                    <Badge
                      badgeContent={tab.badgeCount}
                      color="primary"
                      showZero
                      className="toc_details__tab_badge"
                    >
                      {title}
                    </Badge>
                  ) : (
                    title
                  )
                }
                {...a11yProps(idx)}
                data-cy={tab.dataCy}
              />
            );
          })}
        </Tabs>
      </Box>
      {tabs.map((tab, idx) => (
        <CustomTabPanel key={tab.dataCy} value={value} index={idx}>
          <div className="tab_wrapper__form_wrapper">{tab.component}</div>
        </CustomTabPanel>
      ))}
    </div>
  );
}

export default TabWrapper;
