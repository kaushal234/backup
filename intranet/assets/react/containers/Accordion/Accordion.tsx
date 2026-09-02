import { Box, Collapse } from "@mui/material";
import React, { ReactNode, useState } from "react";
import "./Accordion.css";

interface IProps {
  title: string;
  children: ReactNode;
  isOpenDefault?: boolean;
  hideToggle?: boolean;
}

function Accordion(props: IProps) {
  const { title, children, isOpenDefault = true, hideToggle } = props;

  const [isOpen, setIsOpen] = useState(isOpenDefault);

  return (
    <div className="accordion__main_wrapper ibox float-e-margins">
      <div className="ibox-title">
        <h5>{title}</h5>
        <div className="ibox-tools">
          {!hideToggle && (
            <div className="collapse-link">
              <Box
                className="accordion__toggle_wrapper"
                onClick={() => setIsOpen((prev) => !prev)}
              >
                <i
                  className={`fa ${
                    isOpen ? "fa-chevron-up" : "fa-chevron-down"
                  }`}
                />
              </Box>
            </div>
          )}
        </div>
      </div>
      <Collapse in={isOpen || hideToggle}>
        <div className="ibox-content">{children}</div>
      </Collapse>
    </div>
  );
}

export default Accordion;
