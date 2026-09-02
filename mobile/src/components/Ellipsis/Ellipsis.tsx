import React from "react";
import "./Ellipsis.css";
import { useTranslation } from "react-i18next";

interface IProps {
  children?: string;
  center?: boolean;
  letterSpacing?: number;
}

function Ellipsis(props: IProps) {
  const { children, center, letterSpacing } = props;
  const { t } = useTranslation();

  const translatedText = t(children ?? "");

  return (
    <div
      style={{
        ...(letterSpacing && { letterSpacing: `-${letterSpacing}px` }),
      }}
      className={`ellipsis_wrapper cui_one_line ${center && "ellipsis_center"}`}
    >
      {translatedText.split("").join(" ")}
    </div>
  );
}

export default Ellipsis;
