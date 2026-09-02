import React, { useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useSnackbar } from "notistack";
import { useAppSelector } from "../../hooks/hooks";

function Toast() {
  const { enqueueSnackbar } = useSnackbar();
  const { t } = useTranslation();
  const toastMessage = useAppSelector((state) => state.toast.toastMessage);
  const toastParams = useAppSelector((state) => state.toast.params);
  const toastCounter = useAppSelector((state) => state.toast.counter);

  useEffect(() => {
    if (toastMessage) {
      const message = toastParams
        ? t(toastMessage, toastParams)
        : t(toastMessage);
      enqueueSnackbar(message);
    }
  }, [toastCounter]);

  return <div />;
}

export default Toast;
