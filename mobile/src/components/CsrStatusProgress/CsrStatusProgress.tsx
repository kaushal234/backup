import React from "react";
import { Avatar, IconButton, Paper, Typography } from "@mui/material";
import CreateIcon from "@mui/icons-material/Create";
import { CSR_STATUS_LIST } from "../../constants/constants";
import "./CsrStatusProgress.css";
import CircularProgressWithLabel from "../CircularProgressWithLabel/CircularProgressWithLabel";
import FeatureComponent from "../FeatureComponent/FeatureComponent";

interface IProps {
  status: string;
  showEdit?: boolean;
  onEdit?: () => void;
}

export default function CsrStatusProgress(props: IProps) {
  const {
    status,
    showEdit = false,
    onEdit = () => {
      // empty on purpose
    },
  } = props;

  const activeStep = CSR_STATUS_LIST.findIndex(
    (current) => current.value === status
  );

  return (
    <div className="csr_status_progress">
      <Paper className="csr_status_progress__card">
        <CircularProgressWithLabel
          value={CSR_STATUS_LIST[activeStep].progress}
        />
        <Typography variant="h6" data-cy="csr-status-progress-status">
          {CSR_STATUS_LIST[activeStep].title}
        </Typography>
        {showEdit && (
          <FeatureComponent features={["FEATURE_CUSTOMER_SERVICE_RECORD_EDIT"]}>
            <Avatar className="csr_status_progress__fab_icon" color="primary">
              <IconButton onClick={onEdit} data-cy="csr-status-progress-edit">
                <CreateIcon />
              </IconButton>
            </Avatar>
          </FeatureComponent>
        )}
      </Paper>
    </div>
  );
}
