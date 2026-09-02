import React from "react";
import { Avatar, IconButton, Paper, Typography } from "@mui/material";
import CreateIcon from "@mui/icons-material/Create";
import { TOC_STATUS_LIST } from "../../constants/constants";
import "./TocStatusProgress.css";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";
import CircularProgressWithLabel from "../CircularProgressWithLabel/CircularProgressWithLabel";

interface IProps {
  data: ITechnicianOnCall;
  onEdit: () => void;
}

export default function TocStatusProgress(props: IProps) {
  const { data, onEdit } = props;

  const activeStep = TOC_STATUS_LIST.findIndex(
    (current) => current.value === data.status
  );

  return (
    <div className="toc_status_progress">
      <Paper className="toc_status_progress__card">
        <CircularProgressWithLabel value={activeStep * 20 + 20} />
        <Typography variant="h6" data-cy="toc-status-progress-status">
          {TOC_STATUS_LIST[activeStep].title}
        </Typography>
        <Avatar className="toc_status_progress__fab_icon" color="primary">
          <IconButton onClick={onEdit} data-cy="toc-status-progress-edit">
            <CreateIcon />
          </IconButton>
        </Avatar>
      </Paper>
    </div>
  );
}
