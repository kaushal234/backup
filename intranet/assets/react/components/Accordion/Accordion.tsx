import { Box, Collapse } from "@mui/material";
import React, { ReactNode, useState } from "react";
import "./Accordion.css";
import ReplayIcon from "@mui/icons-material/Replay";
import FilterAltIcon from "@mui/icons-material/FilterAlt";

interface IProps {
  title: string;
  children: ReactNode;
  isOpenDefault?: boolean;
  onFilterClick?: () => void;
  onResetClick?: () => void;
}

function Accordion(props: IProps) {
  const {
    title,
    children,
    isOpenDefault = true,
    onFilterClick,
    onResetClick,
  } = props;

  const [isOpen, setIsOpen] = useState(isOpenDefault);

  return (
    <div className="ibox float-e-margins">
      <div className="ibox-title">
        <h5>{title}</h5>
        <div className="ibox-tools">
          <div className="accordion__actions_wrapper">
            {onFilterClick && (
              <Box
                className="accordion__toggle_wrapper accordion__icon"
                onClick={onFilterClick}
              >
                <FilterAltIcon />
              </Box>
            )}
            {onResetClick && (
              <Box
                className="accordion__toggle_wrapper accordion__icon"
                onClick={onResetClick}
              >
                <ReplayIcon />
              </Box>
            )}
            <Box
              className="accordion__toggle_wrapper"
              onClick={() => setIsOpen((prev) => !prev)}
            >
              <i
                className={`fa ${isOpen ? "fa-chevron-up" : "fa-chevron-down"}`}
              />
            </Box>
          </div>
        </div>
      </div>
      <Collapse in={isOpen}>
        <div className="ibox-content">{children}</div>
      </Collapse>
    </div>
  );
}

export default Accordion;
