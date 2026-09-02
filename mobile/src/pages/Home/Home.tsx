import React, { useEffect, useState } from "react";
import "./Home.css";
import { useTranslation } from "react-i18next";
import {
  IconButton,
  Pagination,
  Typography,
  useMediaQuery,
} from "@mui/material";
import RefreshIcon from "@mui/icons-material/Refresh";
import { useNavigate } from "react-router";
import { getAllTechnicianOnCall } from "../../api/getAllTechnicianOnCall";
import { ITechnicianOnCall } from "../../@type/IGetAllTechnicalOnCallsResponse";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import TocCard from "../../components/TocCard/TocCard";
import { toastError } from "../../utils/api";
import { useDrawer } from "../../hooks/useDrawer";
import { ROUTES } from "../../constants/routes";
import useGlobalTranslate from "../../hooks/useGlobalTranslate";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "drawer.home", link: "" },
];

function Home() {
  useGlobalTranslate();
  useDrawer(ROUTES.home);
  useBreadcrumbs(pageBreadcrumbs);
  const navigate = useNavigate();
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const isLargeScreen = useMediaQuery("(min-width:450px)");
  const userInfo = useAppSelector((store) => store.auth.userInfo);

  const [tocList, setTocList] = useState<Array<ITechnicianOnCall>>([]);
  const itemsPerPage = 5;
  const [page, setPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const pageCount = Math.ceil(totalItems / itemsPerPage) || 1;

  const fetchTocList = async (pageNo = 1) => {
    dispatch(showMainLoader(true));
    const response = await getAllTechnicianOnCall({
      itemsPerPage,
      page: pageNo,
      ...(userInfo?.["@id"] && { assignee: [userInfo["@id"]] }),
    });
    dispatch(showMainLoader(false));
    if (response.data) {
      setTotalItems(response.data["hydra:totalItems"]);
      setTocList(response.data["hydra:member"]);
    } else {
      toastError(dispatch, response);
    }
  };

  useEffect(() => {
    fetchTocList();
  }, []);

  const handlePageChange = (
    event: React.ChangeEvent<unknown>,
    pageNo: number
  ) => {
    fetchTocList(pageNo);
    setPage(pageNo);
  };

  const handleRefresh = () => {
    setPage(1);
    fetchTocList(1);
  };

  const handleCardClick = (item: ITechnicianOnCall) => {
    navigate(`${ROUTES.toc.details}/${item.id}`);
  };

  return (
    <div className="home__wrapper">
      <div className="home__sub_items_bar">
        <Typography
          variant="button"
          gutterBottom
          className="cui_block"
          data-cy="home-results"
        >
          {`${t("common.results")} (${totalItems})`}
        </Typography>
        <div className="home__sub_items_bar_icons">
          <IconButton onClick={handleRefresh} data-cy="home-refresh">
            <RefreshIcon />
          </IconButton>
        </div>
      </div>
      {tocList.map((toc) => (
        <TocCard toc={toc} key={toc.id} onClick={handleCardClick} />
      ))}
      {tocList.length > 0 && (
        <Pagination
          size={isLargeScreen ? "medium" : "small"}
          count={pageCount}
          color="primary"
          page={page}
          onChange={handlePageChange}
          data-cy="home-list-pagination"
        />
      )}
      {tocList.length === 0 && (
        <Typography
          variant="body1"
          className="cui_three_line"
          gutterBottom
          data-cy="home-no-data"
        >
          {t("toc.not_found")}
        </Typography>
      )}
    </div>
  );
}

export default Home;
