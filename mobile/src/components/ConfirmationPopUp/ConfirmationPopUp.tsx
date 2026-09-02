import React from "react";
import "./ConfirmationPopUp.css";
import { Button, Dialog, DialogContentText, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import {
  hideConfirmationPopUp,
  resetConfirmationData,
} from "../../redux/slices/confirmationSlice";
import { confirmationResolve } from "../../hooks/useConfirmation";

export default function ConfirmationPopUp() {
  const dispatch = useAppDispatch();
  const { t } = useTranslation();
  const { title, description, subDescription, isOpen } = useAppSelector(
    (state) => state.confirmation
  );

  const handleResponse = (response: boolean) => {
    confirmationResolve?.(response);
    dispatch(hideConfirmationPopUp());
    setTimeout(() => {
      dispatch(resetConfirmationData());
    }, 100);
  };

  return (
    <Dialog
      closeAfterTransition={false}
      className="confirmation_popup__comment_wrapper"
      onClose={() => handleResponse(false)}
      open={isOpen}
    >
      <Typography
        className="cui_light_text confirmation_popup__heading"
        variant="h5"
        gutterBottom
        data-cy="confirmation-popup-title"
      >
        {t(title)}
      </Typography>
      <DialogContentText
        className="confirmation_popup__warning"
        data-cy="confirmation-popup-description"
      >
        {t(description)}
      </DialogContentText>
      {subDescription && (
        <DialogContentText
          className="confirmation_popup__sub_description"
          data-cy="confirmation-popup-sub-description"
        >
          {subDescription}
        </DialogContentText>
      )}

      <div className="confirmation_popup__button_wrapper">
        <Button
          className="cui_button confirmation_popup__submit_button"
          variant="outlined"
          onClick={() => handleResponse(true)}
          data-cy="confirmation-popup-button-yes"
        >
          {t("confirmation_popup.positive_button")}
        </Button>
        <Button
          className="cui_button confirmation_popup__submit_button"
          variant="outlined"
          onClick={() => handleResponse(false)}
          data-cy="confirmation-popup-button-no"
        >
          {t("confirmation_popup.negative_button")}
        </Button>
      </div>
    </Dialog>
  );
}
