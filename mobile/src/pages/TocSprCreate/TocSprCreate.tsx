import React, { useEffect, useState } from "react";
import "./TocSprCreate.css";
import { useTranslation } from "react-i18next";
import { Paper, Typography } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import TocSprForm from "../../components/TocSprForm/TocSprForm";
import { ITocSprFormData } from "../../@type/ITocSprFormData";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { getSprByTocId } from "../../api/getSprByTocId";
import { IGetSprByTocIdResponse } from "../../@type/IGetSprByTocIdResponse";
import {
  IPostTocSparePartsRequestApiPayload,
  postTocSparePartsRequest,
} from "../../api/postTocSparePartsRequest";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { IDB_DATABASE, TOC_TABS } from "../../constants/constants";
import { toastError } from "../../utils/api";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import { getTechnicianOnCallById } from "../../api/getTechnicianOnCallById";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
  { title: "breadcrumb.toc.details", link: "" },
  { title: "breadcrumb.toc_spr.create", link: "" },
];

function TocSprCreate() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { tocId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const [sprData, setSprData] = useState<IGetSprByTocIdResponse | null>(null);
  const [tocData, setTocData] = useState<ITechnicianOnCall | null>(null);

  const handleSubmit = async (formData: ITocSprFormData) => {
    dispatch(showMainLoader(true));
    if (sprData) {
      const payload: IPostTocSparePartsRequestApiPayload = {
        ...sprData,
        ...formData,
        tocId: +(tocId ?? ""),
        activity: tocData?.serviceActivity.name ?? "",
        type: tocData?.technicianOnCallType.name ?? "",
        technicianOnCall: `/service/technician_on_calls/${tocId ?? ""}`,
      };
      if (isOnline) {
        const response = await postTocSparePartsRequest(payload);
        if (response.status === StatusCodes.CREATED) {
          dispatch(setToastMessage("toc_spr_create.success"));
          navigate(`${ROUTES.toc.details}/${tocId}?tab=${TOC_TABS.parts}`);
        } else {
          toastError(dispatch, response);
        }
      } else {
        await registerSyncEventWithApiPayload({
          eventName: IDB_DATABASE.stores.sync_post_toc_spr,
          payload,
        });
        dispatch(setToastMessage("toc_spr_create.offline"));
        navigate(`${ROUTES.toc.details}/${tocId}?tab=${TOC_TABS.parts}`);
      }
    }
    dispatch(showMainLoader(false));
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "breadcrumb.toc.home", link: ROUTES.toc.home },
      {
        title: "breadcrumb.toc.details",
        link: `${ROUTES.toc.details}/${tocId}`,
        appendText: `(#${tocId ?? ""})`,
      },
      {
        title: "breadcrumb.toc_parts.create",
        link: "",
      },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  const fetchSprAndTocData = async () => {
    if (!tocId) return;
    dispatch(showMainLoader(true));
    const sprPromise = getSprByTocId({ tocId });
    const tocPromise = getTechnicianOnCallById({ id: tocId });
    const [sprResponse, tocResponse] = await Promise.all([
      sprPromise,
      tocPromise,
    ]);
    if (sprResponse.data) {
      setSprData(sprResponse.data);
    }
    if (tocResponse.data) {
      setTocData(tocResponse.data);
    }
  };

  useEffect(() => {
    fetchSprAndTocData();
  }, [tocId]);

  useEffect(() => {
    updateBreadcrumbs();
  }, []);

  if (!sprData || !tocData) return null;

  return (
    <div className="toc_spr_create__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="toc-spr-create-heading"
      >
        {t("toc_spr_create.heading")}
      </Typography>
      <Paper className="toc_spr_create__form">
        <TocSprForm onSubmit={handleSubmit} customers={sprData.customers} />
      </Paper>
    </div>
  );
}

export default TocSprCreate;
