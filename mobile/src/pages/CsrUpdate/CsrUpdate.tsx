import React, { useEffect } from "react";
import "./CsrUpdate.css";
import { useTranslation } from "react-i18next";
import { Paper, Typography } from "@mui/material";
import { useNavigate, useParams } from "react-router";
import { StatusCodes } from "http-status-codes";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setToastMessage } from "../../redux/slices/toastSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { toastError } from "../../utils/api";
import { setBreadcrumbs } from "../../redux/slices/breadcrumbSlice";
import { createAirportDropdownItem } from "../../utils/dropdown/airport";
import { createPeopleDropdownItem } from "../../utils/dropdown/people";
import { CSR_STATUS, IDB_DATABASE } from "../../constants/constants";
import { registerSyncEventWithApiPayload } from "../../utils/serviceWorker";
import CsrForm from "../../components/CsrForm/CsrForm";
import { getCustomerServiceRecordById } from "../../api/getCustomerServiceRecordById";
import { setCsrDetail } from "../../redux/slices/csrDetailSlice";
import { ICsrFormData } from "../../@type/ICsrFormData";
import {
  IPutCustomerServiceRecordApiPayload,
  putCustomerServiceRecord,
} from "../../api/putCustomerServiceRecord";
import { createCsrStatusDropdownItem } from "../../utils/dropdown/csrStatus";
import { createContactDropdownItem } from "../../utils/dropdown/contact";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
  { title: "breadcrumb.csr.details", link: "" },
  { title: "breadcrumb.csr.update", link: "" },
];

function CsrUpdate() {
  useDrawer(ROUTES.csr.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const { csrId } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const data = useAppSelector((state) => state.csrDetail.data);
  const isOnline = useAppSelector((state) => state.networkStatus.isOnline);

  const showTechnicianAndDate =
    data?.status === CSR_STATUS.PENDING ||
    data?.status === CSR_STATUS.PLANNED ||
    data?.status === CSR_STATUS.ASSIGNED;

  const showStatus = data?.status === CSR_STATUS.CLOSED;

  const handleCsrFormSubmit = async (newData: ICsrFormData) => {
    dispatch(showMainLoader(true));
    const params: IPutCustomerServiceRecordApiPayload = {
      "@id": data?.["@id"] ?? "",
      data: {
        title: newData.title,
        description: newData.description,
        airport: newData.airport?.id ?? "",
        ...(showTechnicianAndDate && {
          leader: newData.serviceTechnician?.id ?? null,
          plannedAt: newData.plannedDate ?? null,
        }),
        ...(showStatus && {
          status: newData.status?.id ?? "",
        }),
        contact: newData.contact?.id ?? null,
      },
    };
    if (isOnline) {
      const response = await putCustomerServiceRecord(params);
      if (response.status === StatusCodes.OK && response.data) {
        dispatch(setToastMessage("csr_update.success"));
        navigate(`${ROUTES.csr.details}/${csrId}`);
      } else {
        toastError(dispatch, response);
      }
    } else {
      await registerSyncEventWithApiPayload({
        eventName: IDB_DATABASE.stores.sync_put_csr,
        payload: params,
      });
      dispatch(setToastMessage("csr_update.offline"));
      navigate(ROUTES.csr.home);
    }
    dispatch(showMainLoader(false));
  };

  const fetchCsrDetail = async () => {
    dispatch(showMainLoader(true));
    const response = await getCustomerServiceRecordById({ id: csrId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      dispatch(setCsrDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  const updateBreadcrumbs = () => {
    const newBreadcrumbs: Array<IBreadcrumb> = [
      { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
      {
        title: "breadcrumb.csr.details",
        link: `${ROUTES.csr.details}/${csrId}`,
        appendText: `(#${csrId ?? ""})`,
      },
      { title: "breadcrumb.csr.update", link: "" },
    ];
    dispatch(setBreadcrumbs(newBreadcrumbs));
  };

  useEffect(() => {
    fetchCsrDetail();
    updateBreadcrumbs();
    return () => {
      dispatch(setCsrDetail(null));
    };
  }, []);

  if (!data) return <div />;

  const statusList = [data.status, ...(data.availableStatus ?? [])];
  const statusDropdownItemList = statusList.map((item) =>
    createCsrStatusDropdownItem(item)
  );

  return (
    <div className="csr_update__wrapper">
      <Typography
        variant="h5"
        className="cui_light_text"
        data-cy="csr-update-heading"
      >
        {t("csr_update.heading")}
      </Typography>
      <Paper className="csr_update__form">
        <CsrForm
          onSubmit={handleCsrFormSubmit}
          airport={
            data.airport ? createAirportDropdownItem(data.airport) : null
          }
          title={data.title}
          description={data.description ?? ""}
          serviceTechnician={
            data.openIntervention?.leader
              ? createPeopleDropdownItem(data.openIntervention.leader)
              : null
          }
          plannedDate={data.plannedAt}
          status={createCsrStatusDropdownItem(data.status)}
          contact={
            data.contact ? createContactDropdownItem(data.contact) : null
          }
          statusDropdownItemList={statusDropdownItemList}
          showServiceTechnician={showTechnicianAndDate}
          showPlannedDate={showTechnicianAndDate}
          showStatus={showStatus}
          equipmentRecord={data.equipmentRecord}
        />
      </Paper>
    </div>
  );
}

export default CsrUpdate;
