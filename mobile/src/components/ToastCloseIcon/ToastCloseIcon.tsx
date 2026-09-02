import React from "react";
import IconButton from "@mui/material/IconButton";
import CloseIcon from "@mui/icons-material/Close";
import { SnackbarKey, useSnackbar } from "notistack";

function ToastCloseIcon(snackbarKey: SnackbarKey) {
  const { closeSnackbar } = useSnackbar();
  const handleClose = () => {
    closeSnackbar(snackbarKey);
  };

  return (
    <IconButton
      size="small"
      aria-label="close"
      color="inherit"
      onClick={handleClose}
      data-cy="toast-close"
    >
      <CloseIcon fontSize="small" />
    </IconButton>
  );
}

export default ToastCloseIcon;
