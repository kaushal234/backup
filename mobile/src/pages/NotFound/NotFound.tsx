import React from "react";
import "./NotFound.css";
import { useNavigate } from "react-router";
import { useTranslation } from "react-i18next";
import { Link, Typography } from "@mui/material";
import { ROUTES } from "../../constants/routes";

function NotFound() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleNavigate = () => {
    navigate(ROUTES.home);
  };

  return (
    <div className="not_found__wrapper">
      <Typography className="not_found__title" variant="h3">
        {t("not_found.title")}
      </Typography>
      <Typography variant="body1">{t("not_found.description")}</Typography>
      <Link href="#" onClick={handleNavigate}>
        {t("not_found.link")}
      </Link>
    </div>
  );
}

export default NotFound;
