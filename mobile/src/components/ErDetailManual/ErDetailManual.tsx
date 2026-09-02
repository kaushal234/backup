import React from "react";
import "./ErDetailManual.css";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router";
import {
  IEquipmentRecord,
  IManual,
} from "../../@type/IGetEquipmentRecordResponse";
import ManualCard from "../ManualCard/ManualCard";
import { ROUTES } from "../../constants/routes";

interface IProps {
  data: IEquipmentRecord;
}

export default function ErDetailManual(props: IProps) {
  const { data } = props;
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleClick = (manuel: IManual) => {
    navigate(
      `${ROUTES.er.details}/${data.id}/${ROUTES.er.manual.home}/${manuel.id}`
    );
  };

  return (
    <div className="er_detail_manual__wrapper">
      {!data.manuals.length && (
        <div
          className="er_detail_manual__no_list"
          data-cy="er-manuals-no-content"
        >
          {t("er_details.manuals.no_files")}
        </div>
      )}
      {data.manuals.map((manuel) => (
        <ManualCard data={manuel} onClick={handleClick} key={manuel.id} />
      ))}
    </div>
  );
}
