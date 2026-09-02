import React, { useEffect, useState } from "react";
import "./TocList.css";
import { useTranslation } from "react-i18next";
import {
  Badge,
  IconButton,
  Pagination,
  Typography,
  useMediaQuery,
} from "@mui/material";
import FilterAltIcon from "@mui/icons-material/FilterAlt";
import RefreshIcon from "@mui/icons-material/Refresh";
import { useNavigate } from "react-router";
import SwapVertIcon from "@mui/icons-material/SwapVert";
import { getAllTechnicianOnCall } from "../../api/getAllTechnicianOnCall";
import { ITechnicianOnCall } from "../../@type/IGetAllTechnicalOnCallsResponse";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import TocCard from "../../components/TocCard/TocCard";
import { ROUTES } from "../../constants/routes";
import { toastError } from "../../utils/api";
import { useDrawer } from "../../hooks/useDrawer";
import { formatTocFilters } from "../../utils/utils";
import { IGetAllTechnicalOnCallsFilterRawValues } from "../../@type/IGetAllTechnicalOnCallsFilterRawValues";
import AvatarMenu from "../../components/AvatarMenu/AvatarMenu";
import { IGetAllTechnicianOnCallApiSortPayload } from "../../@type/IGetAllTechnicianOnCallApiSortPayload";
import { SORT_OPTION } from "../../constants/constants";
import { IMenuItem } from "../../@type/IMenuItem";
import FloatingActionButton from "../../components/FloatingActionButton/FloatingActionButton";
import useGlobalTranslate from "../../hooks/useGlobalTranslate";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "drawer.toc.home", link: "" },
];

const countFilters = (filters: IGetAllTechnicalOnCallsFilterRawValues) => {
  let appliedFilterCount = 0;
  Object.values(filters).forEach((filter) => {
    if (Array.isArray(filter) && filter.length) {
      appliedFilterCount++;
    }
    if (typeof filter === "object" && filter && !Array.isArray(filter)) {
      appliedFilterCount++;
    }
    if (typeof filter === "string" && filter) {
      appliedFilterCount++;
    }
  });
  return appliedFilterCount;
};

function TocList() {
  useGlobalTranslate();
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const isLargeScreen = useMediaQuery("(min-width:450px)");

  const [tocList, setTocList] = useState<Array<ITechnicianOnCall>>([]);
  const itemsPerPage = 5;
  const [page, setPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const pageCount = Math.ceil(totalItems / itemsPerPage) || 1;
  const rawTocFilters = useAppSelector((state) => state.tocFilter.filters);
  const [sortOption, setSortOption] =
    useState<IGetAllTechnicianOnCallApiSortPayload>({
      sortByUpdatedAt: SORT_OPTION.DESC,
    });

  const fetchTocList = async (pageNo = 1) => {
    dispatch(showMainLoader(true));
    const response = await getAllTechnicianOnCall({
      itemsPerPage,
      page: pageNo,
      ...formatTocFilters(rawTocFilters),
      ...sortOption,
    });
    dispatch(showMainLoader(false));
    if (response.data) {
      setTotalItems(response.data["hydra:totalItems"]);
      setTocList(response.data["hydra:member"]);
    } else {
      toastError(dispatch, response);
    }
  };

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

  const handleFilter = () => {
    navigate(ROUTES.toc.filter);
  };

  const handleCardClick = (item: ITechnicianOnCall) => {
    navigate(`${ROUTES.toc.details}/${item.id}`);
  };

  const handleSort = (item: IMenuItem) => {
    let newSortOption: IGetAllTechnicianOnCallApiSortPayload = {};
    switch (item.text) {
      case "toc.sort.airport_asc":
        newSortOption = { sortByAirport: SORT_OPTION.ASC };
        break;
      case "toc.sort.airport_desc":
        newSortOption = { sortByAirport: SORT_OPTION.DESC };
        break;
      case "toc.sort.ifactor_asc":
        newSortOption = { sortByIFactor: SORT_OPTION.ASC };
        break;
      case "toc.sort.ifactor_desc":
        newSortOption = { sortByIFactor: SORT_OPTION.DESC };
        break;
      case "toc.sort.created_asc":
        newSortOption = { sortByCreatedAt: SORT_OPTION.ASC };
        break;
      case "toc.sort.created_desc":
        newSortOption = { sortByCreatedAt: SORT_OPTION.DESC };
        break;
      case "toc.sort.updated_asc":
        newSortOption = { sortByUpdatedAt: SORT_OPTION.ASC };
        break;
      case "toc.sort.updated_desc":
        newSortOption = { sortByUpdatedAt: SORT_OPTION.DESC };
        break;

      default:
        break;
    }
    if (JSON.stringify(sortOption) === JSON.stringify(newSortOption)) {
      setSortOption({});
    } else {
      setSortOption(newSortOption);
    }
  };

  const handleAdd = () => {
    navigate(ROUTES.toc.create);
  };

  useEffect(() => {
    fetchTocList(page);
  }, [sortOption]);

  return (
    <div className="toc_list__wrapper">
      <div className="toc_list__sub_items_bar">
        <Typography
          variant="button"
          gutterBottom
          className="cui_block"
          data-cy="toc-list-results"
        >
          {`${t("common.results")} (${totalItems})`}
        </Typography>
        <div className="toc_list__sub_items_bar_icons">
          <div>
            <IconButton onClick={handleRefresh} data-cy="toc-list-refresh">
              <RefreshIcon />
            </IconButton>
          </div>
          <div>
            <IconButton onClick={handleFilter} data-cy="toc-list-filter-icon">
              <Badge badgeContent={countFilters(rawTocFilters)} color="primary">
                <FilterAltIcon />
              </Badge>
            </IconButton>
          </div>
          <div className="toc_list__sort_wrapper">
            <AvatarMenu
              dataCy="toc-list-sort-icon"
              icon={<SwapVertIcon />}
              menuItems={[
                {
                  type: "IMenuItem",
                  text: "toc.sort.airport_asc",
                  selected: sortOption.sortByAirport === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-airport-asc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.airport_desc",
                  selected: sortOption.sortByAirport === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-airport-desc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.ifactor_asc",
                  selected: sortOption.sortByIFactor === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-ifactor-asc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.ifactor_desc",
                  selected: sortOption.sortByIFactor === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-ifactor-desc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.created_asc",
                  selected: sortOption.sortByCreatedAt === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-create-asc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.created_desc",
                  selected: sortOption.sortByCreatedAt === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-create-desc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.updated_asc",
                  selected: sortOption.sortByUpdatedAt === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-update-asc",
                },
                {
                  type: "IMenuItem",
                  text: "toc.sort.updated_desc",
                  selected: sortOption.sortByUpdatedAt === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "toc-list-sort-option-update-asc",
                },
              ]}
            />
          </div>
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
          data-cy="toc-list-pagination"
        />
      )}
      {tocList.length === 0 && (
        <Typography
          variant="body1"
          className="cui_three_line"
          gutterBottom
          data-cy="toc-list-no-data"
        >
          {t("toc.not_found")}
        </Typography>
      )}
      <FloatingActionButton onClick={handleAdd} />
    </div>
  );
}

export default TocList;
