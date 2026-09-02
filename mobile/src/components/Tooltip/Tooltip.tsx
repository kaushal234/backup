import {
  ClickAwayListener,
  ButtonBase,
  Tooltip as MuiTooltip,
} from "@mui/material";
import React, { ReactNode } from "react";
import "./Tooltip.css";

interface IProps {
  children: ReactNode;
  title: string | null;
}

function Tooltip(props: IProps) {
  const { children, title } = props;
  const [open, setOpen] = React.useState(false);

  const toggleTooltip = () => {
    setOpen((prev) => !prev);
  };

  const handleClose = () => {
    setOpen(false);
  };

  return (
    <ClickAwayListener onClickAway={handleClose}>
      <div>
        <MuiTooltip
          className="tooltip__text"
          onClose={handleClose}
          open={open}
          disableFocusListener
          disableHoverListener
          disableTouchListener
          title={title}
          slotProps={{
            popper: {
              disablePortal: true,
            },
          }}
          arrow
          placement="top"
        >
          <ButtonBase onClick={toggleTooltip}>{children}</ButtonBase>
        </MuiTooltip>
      </div>
    </ClickAwayListener>
  );
}

export default Tooltip;
