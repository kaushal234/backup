import React from "react";
import "./Settings.css";
import { useTranslation } from "react-i18next";
import { Button, Paper, Typography } from "@mui/material";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import ResetButton from "../../components/ResetButton/ResetButton";
import { APP_VERSION } from "../../constants/constants";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.home", link: ROUTES.home },
  { title: "breadcrumb.settings", link: "" },
];

function Settings() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();

  const handleTTS = () => {
    window.location.href = process.env.REACT_APP_TTS_LINK ?? "";
  };

  return (
    <div className="settings__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="settings-heading"
      >
        {t("settings.heading")}
      </Typography>
      <Paper className="settings__card">
        <div className="settings__field" data-cy="settings-reset">
          <Typography variant="body1">{t("settings.app.title")}</Typography>
          <Typography className="cui_bold-500" variant="body1">
            {APP_VERSION}
          </Typography>
        </div>
        <div className="settings__field" data-cy="settings-reset">
          <Typography variant="body1">{t("settings.reset.title")}</Typography>
          <ResetButton text="settings.reset.button" />
        </div>
        <div className="settings__field" data-cy="settings-tts">
          <Typography variant="body1">{t("settings.tts.title")}</Typography>
          <Button variant="contained" onClick={handleTTS}>
            {t("settings.tts.button")}
          </Button>
        </div>
      </Paper>
    </div>
  );
}

export default Settings;
