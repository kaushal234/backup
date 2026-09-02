import React, { useEffect } from "react";
import "./ErDetails.css";
import { useParams } from "react-router";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setLastBreadcrumbAppendString } from "../../redux/slices/breadcrumbSlice";
import TabWrapper from "../../components/TabWrapper/TabWrapper";
import { getEquipmentRecordById } from "../../api/getEquipmentRecordById";
import { setErDetail } from "../../redux/slices/erDetailSlice";
import ErDetailDescription from "../../components/ErDetailDescription/ErDetailDescription";
import ErDetailManual from "../../components/ErDetailManual/ErDetailManual";
import ErDetailSchematics from "../../components/ErDetailSchematics/ErDetailSchematics";
import { toastError } from "../../utils/api";

const pageBreadcrumbs = [
  { title: "breadcrumb.er.home", link: ROUTES.er.home },
  { title: "breadcrumb.er.details", link: "" },
];

function ErDetails() {
  useDrawer(ROUTES.er.details);
  useBreadcrumbs(pageBreadcrumbs);
  const { erId } = useParams();
  const dispatch = useAppDispatch();
  const data = useAppSelector((state) => state.erDetail.data);
  const refreshCounter = useAppSelector(
    (state) => state.tocDetail.refreshCounter
  );

  const fetchErDetail = async () => {
    dispatch(showMainLoader(true));
    const response = await getEquipmentRecordById({ id: erId ?? "" });
    dispatch(showMainLoader(false));
    if (response.data) {
      dispatch(setErDetail(response.data));
    } else {
      toastError(dispatch, response);
    }
  };

  useEffect(() => {
    fetchErDetail();
  }, [refreshCounter]);

  useEffect(() => {
    if (data?.serialNumber) {
      dispatch(setLastBreadcrumbAppendString(`(#${data.serialNumber})`));
    }
  }, [data]);

  useEffect(() => {
    return () => {
      dispatch(setErDetail(null));
    };
  }, []);

  if (!data) return <div />;

  return (
    <div className="er_details__wrapper">
      <TabWrapper
        tabs={[
          {
            title: "er_details.tab.description",
            component: <ErDetailDescription data={data} />,
            dataCy: "tab-description",
          },
          {
            title: "er_details.tab.manuals",
            component: <ErDetailManual data={data} />,
            dataCy: "tab-manuals",
          },
          {
            title: "er_details.tab.schematics",
            component: <ErDetailSchematics data={data} />,
            dataCy: "tab-schematics",
          },
        ]}
      />
    </div>
  );
}

export default ErDetails;
