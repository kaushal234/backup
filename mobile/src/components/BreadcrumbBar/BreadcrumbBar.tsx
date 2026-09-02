import React from "react";
import "./BreadcrumbBar.css";
import { Typography } from "@mui/material";
import { useNavigate } from "react-router";
import Breadcrumbs from "@mui/material/Breadcrumbs";
import NavigateNextIcon from "@mui/icons-material/NavigateNext";
import Link from "@mui/material/Link";
import { useTranslation } from "react-i18next";
import { useAppSelector } from "../../hooks/hooks";
import { IBreadcrumb } from "../../@type/IBreadcrumb";

export default function BreadcrumbBar() {
  const { t } = useTranslation();
  const breadcrumbs = useAppSelector((state) => state.breadcrumb.data);
  const navigate = useNavigate();

  const handleNavigate = (
    event: React.MouseEvent<HTMLAnchorElement, MouseEvent>,
    breadcrumb: IBreadcrumb
  ) => {
    event.preventDefault();
    navigate(breadcrumb.link);
  };

  if (!breadcrumbs.length) {
    return <div />;
  }

  return (
    <Breadcrumbs
      className="breadcrumb_bar_wrapper"
      separator={<NavigateNextIcon fontSize="small" />}
      aria-label="breadcrumb"
      data-cy="breadcrumb-bar"
    >
      {breadcrumbs.map((breadcrumb) => {
        return breadcrumb.link ? (
          <Link
            className="cui_label"
            underline="hover"
            key={breadcrumb.title}
            color="inherit"
            href="#"
            onClick={(e) => handleNavigate(e, breadcrumb)}
            data-cy="breadcrumb-bar-link"
          >
            {t(breadcrumb.title)}
            {breadcrumb.appendText && ` ${t(breadcrumb.appendText)}`}
          </Link>
        ) : (
          <Typography
            key={breadcrumb.title}
            sx={{ color: "text.primary" }}
            className="cui_label"
            data-cy="breadcrumb-bar-link"
          >
            {t(breadcrumb.title)}
            {breadcrumb.appendText && ` ${t(breadcrumb.appendText)}`}
          </Typography>
        );
      })}
    </Breadcrumbs>
  );
}
