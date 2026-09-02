import React, { ReactNode } from "react";
import "./FloatingActionButton.css";
import AddIcon from "@mui/icons-material/Add";
import { Avatar, IconButton } from "@mui/material";
import useScrollVisibility from "../../hooks/useScrollVisibility";

interface IProps {
  onClick: () => void;
  icon?: ReactNode;
}

export default function FloatingActionButton(props: IProps) {
  const { onClick, icon } = props;

  const showFab = useScrollVisibility(64);

  if (!showFab) {
    return null;
  }

  return (
    <div className="floating_action_button__fab_wrapper">
      <Avatar className="floating_action_button__fab_icon" color="primary">
        <IconButton onClick={onClick} data-cy="fab-icon">
          {icon || <AddIcon />}
        </IconButton>
      </Avatar>
    </div>
  );
}
