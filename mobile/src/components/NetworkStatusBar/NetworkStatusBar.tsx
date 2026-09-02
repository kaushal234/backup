import React, { useEffect } from "react";
import { useTranslation } from "react-i18next";
import { Alert, Collapse, Divider } from "@mui/material";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setIsOnline } from "../../redux/slices/networkStatus";
import "./NetworkStatusBar.css";

function NetworkStatusBar() {
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);
  const userInfo = useAppSelector((state) => state.auth.userInfo);

  const handleOnline = () => {
    dispatch(setIsOnline(true));
  };

  const handleOffline = () => {
    dispatch(setIsOnline(false));
  };

  useEffect(() => {
    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  return (
    <>
      {!isOnline && <Divider />}
      <Collapse
        in={!isOnline}
        className={`network_status_bar ${
          userInfo
            ? "network_status_bar__wrapper"
            : "network_status_bar__no_user"
        }`}
      >
        <Alert severity="info">{t("offline_alert")}</Alert>
      </Collapse>
    </>
  );
}

export default NetworkStatusBar;
