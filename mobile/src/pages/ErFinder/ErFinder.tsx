import React from "react";
import "./ErFinder.css";
import { Typography } from "@mui/material";
import { useNavigate } from "react-router";
import { useTranslation } from "react-i18next";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import ErSearch from "../../components/ErSearch/ErSearch";
import QRScanner from "../../components/QRScanner/QRScanner";
import { getAllEquipmentRecord } from "../../api/getAllEquipmentRecord";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { toastError } from "../../utils/api";
import { useAppDispatch } from "../../hooks/hooks";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.er.home", link: ROUTES.er.home },
  { title: "breadcrumb.er.finder", link: "" },
];

function ErFinder() {
  useDrawer(ROUTES.er.finder);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  const handleFind = async (text: string) => {
    dispatch(showMainLoader(true));
    const serialNumber = text.split("/").pop() || "";
    const response = await getAllEquipmentRecord({
      serialNumber,
    });
    dispatch(showMainLoader(false));
    const result = response.data?.["hydra:member"];
    if (response.status === StatusCodes.OK && result?.length) {
      navigate(`${ROUTES.er.details}/${result[0].id}`);
    } else if (response.status === StatusCodes.OK && !result?.length) {
      dispatch(setToastMessage("common.error.not_found"));
    } else {
      toastError(dispatch, response);
    }
  };

  return (
    <div className="er_finder__wrapper">
      <ErSearch onSubmit={handleFind} />
      <div className="er_finder__sub_heading">
        <div>
          <Typography variant="body1" gutterBottom>
            {t("er_finder.sub_heading_1")}
          </Typography>
        </div>
        <div>
          <Typography variant="body1" gutterBottom>
            {t("er_finder.sub_heading_2")}
          </Typography>
        </div>
      </div>
      <div className="er_finder__scanner_wrapper">
        <QRScanner onScan={handleFind} />
      </div>
    </div>
  );
}

export default ErFinder;
