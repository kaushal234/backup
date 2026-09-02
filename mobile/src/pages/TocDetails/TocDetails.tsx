import React, { useEffect, useState } from "react";
import "./TocDetails.css";
import { useLocation, useParams } from "react-router";
import HomeOutlinedIcon from "@mui/icons-material/HomeOutlined";
import ChatOutlinedIcon from "@mui/icons-material/ChatOutlined";
import InsertDriveFileOutlinedIcon from "@mui/icons-material/InsertDriveFileOutlined";
import ShoppingCartOutlinedIcon from "@mui/icons-material/ShoppingCartOutlined";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { getTechnicianOnCallById } from "../../api/getTechnicianOnCallById";
import { setTocDetail } from "../../redux/slices/tocDetailSlice";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { setLastBreadcrumbAppendString } from "../../redux/slices/breadcrumbSlice";
import TabWrapper from "../../components/TabWrapper/TabWrapper";
import TocDetailDescription from "../../components/TocDetailDescription/TocDetailDescription";
import DetailLogs from "../../components/DetailLogs/DetailLogs";
import TocDetailFiles from "../../components/TocDetailFiles/TocDetailFiles";
import { getAllComment } from "../../api/getAllCommentById";
import { PAGE_TYPES, TOC_STATUS, TOC_TABS } from "../../constants/constants";
import { setComments } from "../../redux/slices/commentDetailSlice";
import { toastError } from "../../utils/api";
import { setFactoryTime, setNmcTime } from "../../redux/slices/timeSlice";
import { formatFactoryTime, formatNmcTime } from "../../utils/utils";
import TocDetailParts from "../../components/TocDetailParts/TocDetailParts";
import { getNmcTimeById } from "../../api/getNmcTImeById";
import { getFactoryTimeById } from "../../api/getFactoryTImeById";

const pageBreadcrumbs = [
  { title: "drawer.toc.home", link: ROUTES.toc.home },
  { title: "drawer.toc.details", link: "" },
];

function TocDetails() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { tocId } = useParams();
  const location = useLocation();
  const dispatch = useAppDispatch();
  const queryParams = new URLSearchParams(location.search);
  const [tab, setTab] = useState(0);
  const data = useAppSelector((state) => state.tocDetail.data);
  const comments = useAppSelector((state) => state.commentDetail.comments);
  const refreshCounter = useAppSelector(
    (state) => state.tocDetail.refreshCounter
  );

  const fetchTocDetailAndComments = async () => {
    dispatch(showMainLoader(true));
    const tocDetailResponse = await getTechnicianOnCallById({
      id: tocId ?? "",
    });
    const commentsPromise = getAllComment({
      "@id": tocDetailResponse.data?.["@id"] ?? "",
    });
    const factoryTimePromise = getFactoryTimeById({ tocId: tocId ?? "" });
    const nmcTimePromise = getNmcTimeById({ tocId: tocId ?? "" });
    const [commentsResponse, factoryTimeResponse, nmcTimeResponse] =
      await Promise.all([commentsPromise, factoryTimePromise, nmcTimePromise]);
    dispatch(showMainLoader(false));
    if (
      tocDetailResponse.data &&
      commentsResponse.data &&
      factoryTimeResponse.data &&
      nmcTimeResponse.data
    ) {
      dispatch(setTocDetail(tocDetailResponse.data));
      dispatch(setComments(commentsResponse.data["hydra:member"]));
      dispatch(
        setFactoryTime(
          formatFactoryTime(factoryTimeResponse.data["hydra:member"])
        )
      );
      dispatch(setNmcTime(formatNmcTime(nmcTimeResponse.data["hydra:member"])));
    } else {
      toastError(dispatch, tocDetailResponse);
    }
  };

  useEffect(() => {
    fetchTocDetailAndComments();
    dispatch(setLastBreadcrumbAppendString(`(#${tocId})`));
  }, [refreshCounter]);

  useEffect(() => {
    const newTab = queryParams.get("tab");
    if (newTab === TOC_TABS.parts) {
      setTab(3);
    }
    return () => {
      dispatch(setTocDetail(null));
      dispatch(setComments(null));
    };
  }, []);

  if (!data) return <div />;

  const fileCount =
    (data.files?.length ?? 0) +
    (data.mainFile ? 1 : 0) +
    (comments ?? []).reduce((sum, item) => sum + (item.files ?? []).length, 0);

  let partsCount = data.parts?.length ?? 0;

  data.sparePartsRequests.forEach((spr) => {
    partsCount += spr.deletedParts.length;
    partsCount += spr.parts.length;
  });

  return (
    <div className="toc_details__wrapper">
      <TabWrapper
        tabs={[
          {
            title: <HomeOutlinedIcon />,
            component: <TocDetailDescription data={data} />,
            dataCy: "tab-description",
          },
          {
            title: <ChatOutlinedIcon />,
            component: (
              <DetailLogs
                iri={data["@id"]}
                pageType={PAGE_TYPES.TOC}
                comments={comments}
                factoryFlag={data.factoryFlag}
                showFactoryFlag={
                  data.status !== TOC_STATUS.SOLVED &&
                  data.status !== TOC_STATUS.CLOSED
                }
                confidentialToc={data.confidential}
              />
            ),
            dataCy: "tab-logs",
          },
          {
            title: <InsertDriveFileOutlinedIcon />,
            component: <TocDetailFiles data={data} comments={comments} />,
            dataCy: "tab-files",
            badgeCount: fileCount,
          },
          {
            title: <ShoppingCartOutlinedIcon />,
            component: <TocDetailParts data={data} />,
            dataCy: "tab-parts",
            badgeCount: partsCount,
          },
        ]}
        overrideTab={tab}
      />
    </div>
  );
}

export default TocDetails;
