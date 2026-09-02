import React, { useEffect, useState } from "react";
import "./CsrList.css";
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
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { toastError } from "../../utils/api";
import { useDrawer } from "../../hooks/useDrawer";
import { formatCsrFilters } from "../../utils/utils";
import AvatarMenu from "../../components/AvatarMenu/AvatarMenu";
import { SORT_OPTION } from "../../constants/constants";
import { IMenuItem } from "../../@type/IMenuItem";
import { IGetAllCustomerServiceRecordFilterRawValues } from "../../@type/IGetAllCustomerServiceRecordFilterRawValues";
import { ICustomerServiceRecord } from "../../@type/IGetAllCustomerServiceRecordResponse";
import { IGetAllCustomerServiceRecordApiSortPayload } from "../../@type/IGetAllCustomerServiceRecordApiSortPayload";
import { getAllCustomerServiceRecord } from "../../api/getAllICustomerServiceRecord";
import CsrCard from "../../components/CsrCard/CsrCard";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.csr.home", link: "" },
];

const countFilters = (filters: IGetAllCustomerServiceRecordFilterRawValues) => {
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

function CsrList() {
  useDrawer(ROUTES.csr.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const isLargeScreen = useMediaQuery("(min-width:450px)");

  const [csrList, setCsrList] = useState<Array<ICustomerServiceRecord>>([]);
  const itemsPerPage = 5;
  const [page, setPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const pageCount = Math.ceil(totalItems / itemsPerPage) || 1;
  const rawCsrFilters = useAppSelector((state) => state.csrFilter.filters);
  const [sortOption, setSortOption] =
    useState<IGetAllCustomerServiceRecordApiSortPayload>({
      sortByUpdatedAt: SORT_OPTION.DESC,
    });

  const fetchCsrList = async (pageNo = 1) => {
    dispatch(showMainLoader(true));
    const response = await getAllCustomerServiceRecord({
      itemsPerPage,
      page: pageNo,
      ...formatCsrFilters(rawCsrFilters),
      ...sortOption,
    });
    dispatch(showMainLoader(false));
    if (response.data) {
      setTotalItems(response.data["hydra:totalItems"]);
      setCsrList(response.data["hydra:member"]);
    } else {
      toastError(dispatch, response);
    }
  };

  const handlePageChange = (
    event: React.ChangeEvent<unknown>,
    pageNo: number
  ) => {
    fetchCsrList(pageNo);
    setPage(pageNo);
  };

  const handleRefresh = () => {
    setPage(1);
    fetchCsrList(1);
  };

  const handleFilter = () => {
    navigate(ROUTES.csr.filter);
  };

  const handleCardClick = (item: ICustomerServiceRecord) => {
    navigate(`${ROUTES.csr.details}/${item.id}`);
  };

  const handleSort = (item: IMenuItem) => {
    let newSortOption: IGetAllCustomerServiceRecordApiSortPayload = {};
    switch (item.text) {
      case "csr_list.sort.airport_asc":
        newSortOption = { sortByAirport: SORT_OPTION.ASC };
        break;
      case "csr_list.sort.airport_desc":
        newSortOption = { sortByAirport: SORT_OPTION.DESC };
        break;
      case "csr_list.sort.created_asc":
        newSortOption = { sortByCreatedAt: SORT_OPTION.ASC };
        break;
      case "csr_list.sort.created_desc":
        newSortOption = { sortByCreatedAt: SORT_OPTION.DESC };
        break;
      case "csr_list.sort.updated_asc":
        newSortOption = { sortByUpdatedAt: SORT_OPTION.ASC };
        break;
      case "csr_list.sort.updated_desc":
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

  useEffect(() => {
    fetchCsrList(page);
  }, [sortOption]);

  return (
    <div className="csr_list__wrapper">
      <div className="csr_list__sub_items_bar">
        <Typography
          variant="button"
          gutterBottom
          className="cui_block"
          data-cy="csr-list-results"
        >
          {`${t("common.results")} (${totalItems})`}
        </Typography>
        <div className="csr_list__sub_items_bar_icons">
          <div>
            <IconButton onClick={handleRefresh} data-cy="csr-list-refresh">
              <RefreshIcon />
            </IconButton>
          </div>
          <div>
            <IconButton onClick={handleFilter} data-cy="csr-list-filter-icon">
              <Badge badgeContent={countFilters(rawCsrFilters)} color="primary">
                <FilterAltIcon />
              </Badge>
            </IconButton>
          </div>
          <div className="csr_list__sort_wrapper">
            <AvatarMenu
              dataCy="csr-list-sort-icon"
              icon={<SwapVertIcon />}
              menuItems={[
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.airport_asc",
                  selected: sortOption.sortByAirport === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-airport-asc",
                },
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.airport_desc",
                  selected: sortOption.sortByAirport === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-airport-desc",
                },
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.created_asc",
                  selected: sortOption.sortByCreatedAt === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-create-asc",
                },
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.created_desc",
                  selected: sortOption.sortByCreatedAt === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-create-desc",
                },
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.updated_asc",
                  selected: sortOption.sortByUpdatedAt === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-update-asc",
                },
                {
                  type: "IMenuItem",
                  text: "csr_list.sort.updated_desc",
                  selected: sortOption.sortByUpdatedAt === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "csr-list-sort-option-update-asc",
                },
              ]}
            />
          </div>
        </div>
      </div>
      {csrList.map((csr) => (
        <CsrCard csr={csr} key={csr.id} onClick={handleCardClick} />
      ))}
      {csrList.length > 0 && (
        <Pagination
          size={isLargeScreen ? "medium" : "small"}
          count={pageCount}
          color="primary"
          page={page}
          onChange={handlePageChange}
          data-cy="csr-list-pagination"
        />
      )}
      {csrList.length === 0 && (
        <Typography
          variant="body1"
          className="cui_three_line"
          gutterBottom
          data-cy="csr-list-no-data"
        >
          {t("csr_list.not_found")}
        </Typography>
      )}
    </div>
  );
}

export default CsrList;
