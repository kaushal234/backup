import React from "react";
import { Button } from "@mui/material";
import { useTranslation } from "react-i18next";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { useConfirmation } from "../../hooks/useConfirmation";
import { resetWebsite } from "../../utils/utils";

interface IProps {
  text: string;
}

export default function ResetButton(props: IProps) {
  const { t } = useTranslation();
  const { text } = props;
  const dispatch = useAppDispatch();
  const { confirmation } = useConfirmation();

  const handleReset = async () => {
    const response = await confirmation(
      "settings.reset.popup.title",
      "settings.reset.popup.description"
    );
    if (response) {
      dispatch(showMainLoader(true));
      await resetWebsite();
      window.location.reload();
    }
  };

  return (
    <Button variant="contained" onClick={handleReset}>
      {t(text)}
    </Button>
  );
}
