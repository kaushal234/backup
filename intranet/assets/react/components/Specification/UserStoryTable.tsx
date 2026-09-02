import React, { useEffect, useState } from "react";
import { Accordion, Button, Table } from "react-bootstrap";
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table";
import { removeTags, shortenString } from "../../utils/functions";
import { DebouncedInput } from "../../utils/DebouncedInput";

interface IProps {
  userStories: [IUserStory];
  onShowDetail: any;
  selectedRow: any;
}

interface IUserStory {
  "@id": string;
  "@type": string;
  createdBy: ICreateBy;
  category: string;
  description: string;
  id: number;
  status: string;
}

interface ICreateBy {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
}

function UserStoryTable({ userStories, onShowDetail, selectedRow }: IProps) {
  const columnHelper = createColumnHelper<IUserStory>();

  const columns: any = [
    columnHelper.accessor("id", {
      header: "ID",
      cell: (info) => info.getValue(),
      filterFn: "equalsString",
    }),
    columnHelper.accessor("category", {
      header: "Category",
      cell: (info) =>
        info.getValue().charAt(0).toUpperCase() +
        info.getValue().slice(1).toLowerCase(),
      filterFn: "includesString",
    }),
    columnHelper.accessor("description", {
      header: "Description",
      cell: (info) => removeTags(shortenString(info.renderValue(), 115)),
      filterFn: "includesStringSensitive",
    }),
    columnHelper.accessor("status", {
      header: "Status",
      cell: (info) =>
        info.getValue().charAt(0).toUpperCase() +
        info.getValue().slice(1).toLowerCase(),
      filterFn: "includesString",
    }),
    columnHelper.accessor(
      (row: IUserStory) =>
        `${row.createdBy.firstname} ${row.createdBy.lastname}`,
      {
        id: "createdBy",
        header: "Created by",
        cell: (info) => info.getValue(), // déjà une string
        filterFn: "includesString",
      }
    ),
    columnHelper.display({
      id: "detail",
      header: "Detail",
      cell: (info) => (
        <Button
          variant="primary"
          className="px-2 py-0 my-1"
          onClick={() => onShowDetail(info.row.getValue("id"))}
        >
          <i className="fa fa-eye" />
        </Button>
      ),
      enableSorting: false,
    }),
  ];

  const [data, setData] = useState(() => [...userStories]);

  const [sorting, setSorting] = useState([
    {
      id: "id",
      desc: true,
    },
  ]);

  const [globalFilter, setGlobalFilter] = React.useState("");

  useEffect(() => {
    setData(userStories);
  }, [userStories]);

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    onSortingChange: setSorting,
    enableSortingRemoval: false,
    state: {
      globalFilter,
      sorting,
    },
    onGlobalFilterChange: setGlobalFilter,
    getFilteredRowModel: getFilteredRowModel(), // client side filtering
  });

  return userStories && userStories.length > 0 ? (
    <Accordion defaultActiveKey="0">
      <Accordion.Item eventKey="0">
        <Accordion.Header className="ibox-title">
          <h5>User Story</h5>
        </Accordion.Header>
        <Accordion.Body className="ibox-content p-0">
          <div
            className="input-group search-table position-sticky py-3 bg-white mb-0"
            style={{ top: "0" }}
          >
            <div className="input-group-text">
              <i className="fa fa-search" />
            </div>
            <DebouncedInput
              value={globalFilter ?? ""}
              onChange={(value: any) => setGlobalFilter(value)}
              placeholder="Search in table..."
              className="form-control"
            />
          </div>
          <Table
            hover
            striped
            className="table-responsive report-table tooltip-container"
          >
            <thead
              className="sticky-top"
              style={{ backgroundColor: "#004F9E", color: "#fff", top: "70px" }}
            >
              {table.getHeaderGroups().map((headerGroup) => (
                <tr key={headerGroup.id}>
                  {headerGroup.headers.map((header: any) => {
                    return (
                      <th
                        key={header.id}
                        className="py-1 border-right border-1 py-1"
                        onClick={header.column.getToggleSortingHandler()}
                      >
                        {header.isPlaceholder ? null : (
                          <div
                            title={
                              header.column.getCanSort() &&
                              header.column.getNextSortingOrder() === "asc"
                                ? "Sort ascending"
                                : "Sort descending"
                            }
                          >
                            {flexRender(
                              header.column.columnDef.header,
                              header.getContext()
                            )}
                            &nbsp;
                            {(
                              {
                                asc: (
                                  <i
                                    className="fa fa-chevron-up align-middle"
                                    style={{ display: "inline" }}
                                  />
                                ),
                                desc: (
                                  <i
                                    className="fa fa-chevron-down"
                                    style={{ display: "inline" }}
                                  />
                                ),
                              } as any
                            )[header.column.getIsSorted()] ?? null}
                          </div>
                        )}
                      </th>
                    );
                  })}
                </tr>
              ))}
            </thead>
            <tbody>
              {table.getRowModel().rows.map((row) => (
                <tr
                  key={row.id}
                  className="ty-1"
                  style={
                    selectedRow === row.getValue("id")
                      ? {
                          backgroundColor: "#b5bdc2",
                          color: "#000",
                        }
                      : {}
                  }
                >
                  {row.getVisibleCells().map((cell) => (
                    <td
                      key={cell.id}
                      className="align-middle py-0 border-right border-1"
                    >
                      {flexRender(
                        cell.column.columnDef.cell,
                        cell.getContext()
                      )}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </Table>
          <hr />
        </Accordion.Body>
      </Accordion.Item>
    </Accordion>
  ) : (
    <div>No user stories found</div>
  );
}
export default UserStoryTable;
