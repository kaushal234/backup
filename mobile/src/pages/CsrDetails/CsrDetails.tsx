import React, { useEffect } from "react";
import "./CsrDetails.css";
import { useParams } from "react-router";
import HomeOutlinedIcon from "@mui/icons-material/HomeOutlined";
import ChatOutlinedIcon from "@mui/icons-material/ChatOutlined";
import InsertDriveFileOutlinedIcon from "@mui/icons-material/InsertDriveFileOutlined";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { setCsrDetail } from "../../redux/slices/csrDetailSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setLastBreadcrumbAppendString } from "../../redux/slices/breadcrumbSlice";
import TabWrapper from "../../components/TabWrapper/TabWrapper";
import { getCustomerServiceRecordById } from "../../api/getCustomerServiceRecordById";
import CsrDetailDescription from "../../components/CsrDetailDescription/CsrDetailDescription";
import { getAllComment } from "../../api/getAllCommentById";
import { PAGE_TYPES, TOC_STATUS } from "../../constants/constants";
import DetailLogs from "../../components/DetailLogs/DetailLogs";
import { setComments } from "../../redux/slices/commentDetailSlice";
import CsrDetailFiles from "../../components/CsrDetailFiles/CsrDetailFiles";
import { toastError } from "../../utils/api";
import { getFactoryTimeById } from "../../api/getFactoryTImeById";
import { setFactoryTime } from "../../redux/slices/timeSlice";
import { formatFactoryTime } from "../../utils/utils";
import { IGetAuditLogFullResponse } from "../../api/getAuditLog";

const pageBreadcrumbs = [
  { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
  { title: "breadcrumb.csr.details", link: "" },
];

function CsrDetails() {
  useDrawer(ROUTES.csr.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { csrId } = useParams();
  const dispatch = useAppDispatch();
  const data = useAppSelector((state) => state.csrDetail.data);
  const comments = useAppSelector((state) => state.commentDetail.comments);
  const refreshCounter = useAppSelector(
    (state) => state.csrDetail.refreshCounter
  );

  const fetchCsrDetailAndComments = async () => {
    dispatch(showMainLoader(true));
    const csrDetailResponse = await getCustomerServiceRecordById({
      id: csrId ?? "",
    });
    const commentsResponse = await getAllComment({
      "@id": csrDetailResponse.data?.["@id"] ?? "",
    });
    let factoryTimeResponse: IGetAuditLogFullResponse | null = null;
    if (csrDetailResponse.data?.technicianOnCall) {
      factoryTimeResponse = await getFactoryTimeById({
        tocId: csrDetailResponse.data.technicianOnCall.id?.toString() ?? "",
      });
    }
    dispatch(showMainLoader(false));
    if (csrDetailResponse.data && commentsResponse.data) {
      dispatch(setCsrDetail(csrDetailResponse.data));
      dispatch(setComments(commentsResponse.data["hydra:member"]));
      if (factoryTimeResponse?.data) {
        dispatch(
          setFactoryTime(
            formatFactoryTime(factoryTimeResponse.data["hydra:member"])
          )
        );
      }
    } else {
      toastError(dispatch, csrDetailResponse);
    }
  };

  useEffect(() => {
    fetchCsrDetailAndComments();
    dispatch(setLastBreadcrumbAppendString(`(#${csrId})`));
  }, [refreshCounter]);

  useEffect(() => {
    return () => {
      dispatch(setCsrDetail(null));
      dispatch(setComments(null));
    };
  }, []);

  if (!data) return <div />;

  return (
    <div className="csr_details__wrapper">
      <TabWrapper
        tabs={[
          {
            title: <HomeOutlinedIcon />,
            component: <CsrDetailDescription data={data} />,
            dataCy: "tab-description",
          },
          {
            title: <ChatOutlinedIcon />,
            component: (
              <DetailLogs
                iri={data["@id"]}
                pageType={PAGE_TYPES.CSR}
                comments={comments}
                factoryFlag={data.technicianOnCall?.factoryFlag}
                showFactoryFlag={
                  data.technicianOnCall &&
                  !data.technicianOnCall.factoryFlag &&
                  data.technicianOnCall.status !== TOC_STATUS.SOLVED &&
                  data.technicianOnCall.status !== TOC_STATUS.CLOSED
                }
                linkedTocIri={data.technicianOnCall?.["@id"]}
                confidentialToc={!!data.technicianOnCall?.confidential}
              />
            ),
            dataCy: "tab-logs",
          },
          {
            title: <InsertDriveFileOutlinedIcon />,
            component: <CsrDetailFiles comments={comments} data={data} />,
            dataCy: "tab-files",
            badgeCount: data.files.length ?? 0,
          },
        ]}
      />
    </div>
  );
}

export default CsrDetails;
