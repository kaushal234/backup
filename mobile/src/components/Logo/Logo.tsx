import React from "react";
import { useTheme } from "@mui/material";
import LogoDark from "../../assets/logo-dark.png";
import LogoLight from "../../assets/logo-light.png";

interface IProps {
  className?: string;
  dataCy?: string;
}

export default function Logo(props: IProps) {
  const { className, dataCy } = props;
  const theme = useTheme();
  const { mode } = theme.palette;

  if (mode === "light") {
    return (
      <img
        className={className}
        src={LogoLight}
        alt="Alvest Logo"
        data-cy={dataCy}
      />
    );
  }
  return (
    <img
      className={className}
      src={LogoDark}
      alt="Alvest Logo"
      data-cy={dataCy}
    />
  );
}
