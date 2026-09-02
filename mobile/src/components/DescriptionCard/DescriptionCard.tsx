import React from "react";
import "./DescriptionCard.css";
import { Box, Paper, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router";

interface IProps {
  title: string;
  value?: string | null;
  className?: string;
  link?: string;
  dataCy?: string;
  onClick?: () => void;
}

export default function DescriptionCard(props: IProps) {
  const { title, value, className, link, dataCy, onClick } = props;
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleClick = () => {
    if (link?.startsWith("#")) {
      const id = link.substring(1);
      const element = document.getElementById(id);
      element?.scrollIntoView({ behavior: "smooth" });
    } else if (link) {
      navigate(link);
    }
    onClick?.();
  };

  return (
    <Box
      className={`${(link || onClick) && "description_card__wrapper_clicker"}`}
      onClick={handleClick}
    >
      <Paper elevation={1} className={`description_card__wrapper ${className}`}>
        <div className="description_card__tile">
          <Typography
            variant="caption"
            gutterBottom
            sx={{ display: "block" }}
            data-cy={`${dataCy}-title`}
          >
            {t(title)}
          </Typography>
          <Typography
            className="description_card__value"
            variant="h6"
            gutterBottom
            sx={{ display: "block" }}
            data-cy={`${dataCy}-value`}
          >
            {value || "---"}
          </Typography>
        </div>
      </Paper>
    </Box>
  );
}
