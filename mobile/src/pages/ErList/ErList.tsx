import React, { useEffect, useState } from "react";
import "./ErList.css";
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
import AvatarMenu from "../../components/AvatarMenu/AvatarMenu";
import { SORT_OPTION } from "../../constants/constants";
import { IMenuItem } from "../../@type/IMenuItem";
import { getAllEquipmentRecord } from "../../api/getAllEquipmentRecord";
import { IEquipmentRecord } from "../../@type/IGetAllEquipmentRecordResponse";
import ErCard from "../../components/ErCard/ErCard";
import { IGetAllEquipmentRecordFilterRawValues } from "../../@type/IGetAllEquipmentRecordFilterRawValues";
import { formatErFilters } from "../../utils/utils";
import { IGetAllEquipmentRecordApiSortPayload } from "../../@type/IGetAllEquipmentRecordApiSortPayload";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.er.home", link: "" },
];

const countFilters = (filters: IGetAllEquipmentRecordFilterRawValues) => {
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

function ErList() {
  useDrawer(ROUTES.er.home);
  useBreadcrumbs(pageBreadcrumbs);
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const isLargeScreen = useMediaQuery("(min-width:450px)");

  const [erList, setErList] = useState<Array<IEquipmentRecord>>([]);
  const itemsPerPage = 5;
  const [page, setPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const pageCount = Math.ceil(totalItems / itemsPerPage) || 1;
  const rawErFilters = useAppSelector((state) => state.erFilter.filters);
  const [sortOption, setSortOption] =
    useState<IGetAllEquipmentRecordApiSortPayload>({});

  const fetchErList = async (pageNo = 1) => {
    dispatch(showMainLoader(true));
    const response = await getAllEquipmentRecord({
      itemsPerPage,
      page: pageNo,
      ...formatErFilters(rawErFilters),
      ...sortOption,
    });
    dispatch(showMainLoader(false));
    if (response.data) {
      setTotalItems(response.data["hydra:totalItems"]);
      setErList(response.data["hydra:member"]);
    } else {
      toastError(dispatch, response);
    }
  };

  const handlePageChange = (
    event: React.ChangeEvent<unknown>,
    pageNo: number
  ) => {
    fetchErList(pageNo);
    setPage(pageNo);
  };

  const handleRefresh = () => {
    setPage(1);
    fetchErList(1);
  };

  const handleFilter = () => {
    navigate(ROUTES.er.filter);
  };

  const handleCardClick = (item: IEquipmentRecord) => {
    navigate(`${ROUTES.er.details}/${item.id}`);
  };

  const handleSort = (item: IMenuItem) => {
    let newSortOption: IGetAllEquipmentRecordApiSortPayload = {};
    switch (item.text) {
      case "er_list.sort.serial_number_asc":
        newSortOption = { sortBySerialNumber: SORT_OPTION.ASC };
        break;
      case "er_list.sort.serial_number_desc":
        newSortOption = { sortBySerialNumber: SORT_OPTION.DESC };
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
    fetchErList(page);
  }, [sortOption]);

  return (
    <div className="er_list__wrapper">
      <div className="er_list__sub_items_bar">
        <Typography
          variant="button"
          gutterBottom
          className="cui_block"
          data-cy="er-list-results"
        >
          {`${t("common.results")} (${totalItems})`}
        </Typography>
        <div className="er_list__sub_items_bar_icons">
          <div>
            <IconButton onClick={handleRefresh} data-cy="er-list-refresh">
              <RefreshIcon />
            </IconButton>
          </div>
          <div>
            <IconButton onClick={handleFilter} data-cy="er-list-filter-icon">
              <Badge badgeContent={countFilters(rawErFilters)} color="primary">
                <FilterAltIcon />
              </Badge>
            </IconButton>
          </div>
          <div className="er_list__sort_wrapper">
            <AvatarMenu
              dataCy="er-list-sort-icon"
              icon={<SwapVertIcon />}
              menuItems={[
                {
                  type: "IMenuItem",
                  text: "er_list.sort.serial_number_asc",
                  selected: sortOption.sortBySerialNumber === SORT_OPTION.ASC,
                  onClick: handleSort,
                  dataCy: "er-list-sort-option-serial-number-asc",
                },
                {
                  type: "IMenuItem",
                  text: "er_list.sort.serial_number_desc",
                  selected: sortOption.sortBySerialNumber === SORT_OPTION.DESC,
                  onClick: handleSort,
                  dataCy: "er-list-sort-option-serial-number-desc",
                },
              ]}
            />
          </div>
        </div>
      </div>
      {erList.map((er) => (
        <ErCard data={er} key={er.id} onClick={handleCardClick} />
      ))}
      {erList.length > 0 && (
        <Pagination
          size={isLargeScreen ? "medium" : "small"}
          count={pageCount}
          color="primary"
          page={page}
          onChange={handlePageChange}
          data-cy="er-list-pagination"
        />
      )}
      {erList.length === 0 && (
        <Typography
          variant="body1"
          className="cui_three_line"
          gutterBottom
          data-cy="er-list-no-data"
        >
          {t("er_list.not_found")}
        </Typography>
      )}
    </div>
  );
}

export default ErList;
