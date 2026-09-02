import React, { ReactNode } from "react";
import "./InfoCard.css";
import { useTranslation } from "react-i18next";
import { Typography } from "@mui/material";

interface IProps {
  data: Array<IItem>;
  footer?: ReactNode;
  dataCy?: string;
}

interface IItem {
  title: string;
  value: ReactNode;
}

export default function InfoCard(props: IProps) {
  const { data, footer, dataCy } = props;
  const { t } = useTranslation();

  return (
    <div className="info_card__wrapper" data-cy={dataCy}>
      <div className="info_card__content">
        {data.map((item) => (
          <div className="info_card__row">
            <div className="info_card__title" data-cy={`${dataCy}-title`}>
              <Typography variant="body1">{t(item.title)}</Typography>
            </div>
            <div className="info_card__value" data-cy={`${dataCy}-value`}>
              {item.value}
            </div>
          </div>
        ))}
        {footer}
      </div>
    </div>
  );
}
