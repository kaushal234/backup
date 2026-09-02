import React, { ReactNode } from "react";
import { ButtonBase, ClickAwayListener, Tooltip } from "@mui/material";
import "./MobileTooltip.css";
import { useTranslation } from "react-i18next";

interface IProps {
  children: ReactNode;
  title: string;
}

function MobileTooltip(props: IProps) {
  const { children, title } = props;
  const { t } = useTranslation();
  const [open, setOpen] = React.useState(false);

  const handleTooltipClose = () => {
    setOpen(false);
  };

  const handleTooltipOpen = () => {
    setOpen(true);
  };

  return (
    <div className="mobile-tooltip-wrapper">
      <ClickAwayListener onClickAway={handleTooltipClose}>
        <Tooltip
          onClose={handleTooltipClose}
          open={open}
          title={t(title)}
          arrow
          placement="top"
          onMouseEnter={handleTooltipOpen}
          onMouseLeave={handleTooltipClose}
          slotProps={{
            tooltip: {
              sx: {
                margin: "8px",
                textAlign: "center",
              },
            },
          }}
        >
          <ButtonBase
            disableRipple
            onClick={(e) => {
              e.stopPropagation();
              handleTooltipOpen();
            }}
          >
            {children}
          </ButtonBase>
        </Tooltip>
      </ClickAwayListener>
    </div>
  );
}

export default MobileTooltip;
