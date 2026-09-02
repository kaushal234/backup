import React from "react";
import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import "./DataTableView.css";
import Translator from "bazinga-translator";
import {
  CircularProgress,
  TableFooter,
  TablePagination,
  TableSortLabel,
} from "@mui/material";
import { PAGE_SIZE_OPTIONS } from "../../constants/constants";
import { IDataTableHeaderItem } from "../../types/IDataTableHeaderItem";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { dataTableActions } from "../../reducers/dataTable/dataTableSlice";
import { ISortModal } from "../../types/ISortModal";
import { saveDataTableSettings } from "../../utils/utils";
import { IPagination } from "../../types/IPagination";

export interface IDataTableViewProps {
  name: string;
  headers: Array<IDataTableHeaderItem>;
  rows: Array<Array<React.ReactNode | string>>;
  noContentMessage?: string;
  rowCount?: number;
}

export default function DataTableView(props: IDataTableViewProps) {
  const { headers, rows, noContentMessage, rowCount, name } = props;

  const dispatch = useAppDispatch();

  const { isLoading, sortModel, pagination } = useAppSelector(
    (state) => state.dataTable
  );

  const positionApplied = headers[0]?.position !== undefined;

  let finalHeaders = [...headers];
  let finalRows = [...rows];
  if (positionApplied) {
    finalHeaders = [];
    headers.forEach((header, idx) => {
      if (header.position !== undefined && header.position !== -1) {
        finalHeaders[header.position] = headers[idx];
      }
    });
    finalRows = rows.map((row) => {
      const newRow: Array<React.ReactNode> = [];
      headers.forEach((header, idx) => {
        if (header.position !== undefined && header.position !== -1) {
          newRow[header.position] = row[idx];
        }
      });
      return newRow;
    });
  }

  const handlePageChange = async (
    event: React.MouseEvent<HTMLButtonElement> | null,
    newPage: number
  ) => {
    const newPagination: IPagination = { ...pagination, page: newPage };
    dispatch(dataTableActions.setPagination(newPagination));
    await saveDataTableSettings({
      name,
      settings: { pagination: newPagination },
    });
  };

  const handleRowsPerPageChange = async (
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const newPagination: IPagination = {
      itemsPerPage: parseInt(event.target.value, 10),
      page: 0,
    };
    dispatch(dataTableActions.setPagination(newPagination));
    await saveDataTableSettings({
      name,
      settings: { pagination: newPagination },
    });
  };

  const handleSortChange = async (field: string) => {
    let newSortModal: ISortModal | null;
    if (field !== sortModel?.field) {
      newSortModal = { field, sort: "asc" };
    } else {
      switch (sortModel?.sort) {
        case undefined:
          newSortModal = { field, sort: "asc" };
          break;
        case "asc":
          newSortModal = { field, sort: "desc" };
          break;
        default:
          newSortModal = null;
      }
    }
    dispatch(dataTableActions.setSortModal(newSortModal));
    await saveDataTableSettings({
      name,
      settings: { sortModel: newSortModal },
    });
  };

  return (
    <TableContainer className="data_table_view__wrapper">
      <Table stickyHeader>
        <TableHead>
          <TableRow>
            {finalHeaders.map((header) => {
              if (header.isHidden) return null;
              return (
                <TableCell
                  className="data_table_view__no_wrap"
                  key={header.value}
                  sx={{
                    minWidth: header.minWidth ?? "120px",
                  }}
                >
                  {header.sortFieldName ? (
                    <TableSortLabel
                      active={
                        sortModel?.field === header.sortFieldName &&
                        !!sortModel.sort
                      }
                      direction={
                        sortModel?.field === header.sortFieldName
                          ? sortModel.sort
                          : "asc"
                      }
                      onClick={() =>
                        handleSortChange(header.sortFieldName ?? "")
                      }
                    >
                      {Translator.trans(header.value)}
                    </TableSortLabel>
                  ) : (
                    Translator.trans(header.value)
                  )}
                </TableCell>
              );
            })}
          </TableRow>
        </TableHead>
        <TableBody>
          {finalRows.map((row, idx) => (
            <TableRow
              // eslint-disable-next-line react/no-array-index-key
              key={idx}
              sx={{ "&:last-child td, &:last-child th": { border: 0 } }}
            >
              {row.map((cell, cellIdx) => {
                if (finalHeaders[cellIdx].isHidden) return null;
                return (
                  <TableCell
                    // eslint-disable-next-line react/no-array-index-key
                    key={`${cellIdx}-${cell}`}
                    className={`${finalHeaders[cellIdx].className} ${
                      idx === rows.length - 1 &&
                      isLoading &&
                      "data_table_view__no_border"
                    }`}
                    sx={{
                      width: finalHeaders[cellIdx].minWidth,
                      maxWidth: finalHeaders[cellIdx].minWidth,
                    }}
                  >
                    {cell || "---"}
                  </TableCell>
                );
              })}
            </TableRow>
          ))}
          {rows.length === 0 && noContentMessage && !isLoading && (
            <TableRow
              sx={{ "&:last-child td, &:last-child th": { border: 0 } }}
            >
              <TableCell colSpan={finalHeaders.length}>
                {Translator.trans(noContentMessage ?? "")}
              </TableCell>
            </TableRow>
          )}
          {isLoading && (
            <>
              <TableRow className="data_table_view__loading_row">
                <TableCell>
                  <div className="data_table_view__loading_wrapper">
                    <CircularProgress />
                  </div>
                </TableCell>
              </TableRow>
              {rows.length === 0 && (
                <TableRow>
                  <TableCell colSpan={finalHeaders.length}>
                    <div className="data_table_view__loader_pad" />
                  </TableCell>
                </TableRow>
              )}
            </>
          )}
        </TableBody>
        {pagination && (
          <TableFooter>
            <TableRow>
              <TablePagination
                rowsPerPageOptions={PAGE_SIZE_OPTIONS}
                count={rowCount ?? 0}
                rowsPerPage={pagination?.itemsPerPage ?? PAGE_SIZE_OPTIONS[0]}
                page={pagination?.page ?? 0}
                onPageChange={handlePageChange}
                onRowsPerPageChange={handleRowsPerPageChange}
                disabled={isLoading}
              />
            </TableRow>
          </TableFooter>
        )}
      </Table>
    </TableContainer>
  );
}
