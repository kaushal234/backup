import React, { useEffect, useState } from "react";
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table";
import { Accordion, Button, Table } from "react-bootstrap";
import moment from "moment";
import Swal from "sweetalert2";
import { DebouncedInput } from "../../utils/DebouncedInput";

interface IProps {
  troubleTicketsByModule: any;
  onTSSAddToList: any;
}

function TroubleTicketsTable({
  troubleTicketsByModule,
  onTSSAddToList,
}: IProps) {
  const handleShowAlert = (TTSId: any) => {
    Swal.fire({
      icon: "warning",
      text: 'If you press "YES", then this item will no longer appear in any specification. This action cannot be undone.',
      title: "Remove TTS from list ?",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "YES",
      cancelButtonText: "NO",
    }).then((result) => {
      if (result.isConfirmed) {
        onTSSAddToList(TTSId);
      }
    });
  };
  const formatDate = (dateString: any) => {
    return moment(dateString).format("YYYY-MM-DD");
  };

  const columnHelper = createColumnHelper();

  const columns: any = [
    columnHelper.accessor("id", {
      header: "ID",
      cell: (info: any) => (
        <a href={`/en/private/mis/trouble-tickets/${info.getValue()}/show`}>
          {info.getValue()}
        </a>
      ),
      filterFn: "equalsString",
    }),
    columnHelper.accessor("status", {
      header: "Status",
      cell: (info) => info.getValue(),
      filterFn: "includesString",
    }),
    columnHelper.accessor("type.type", {
      header: "Type",
      cell: (info) => info.getValue(),
      filterFn: "includesString",
    }),
    columnHelper.accessor("createdAt ", {
      header: () => <span className="text-break">Created At</span>,
      cell: (info) => formatDate(info.getValue()),
    }),
    columnHelper.accessor(
      (row: any) => `${row.createdBy.firstname} ${row.createdBy.lastname}`,
      {
        id: "createBy",
        header: () => <span className="text-break">Created At</span>,
        cell: (info: any) => (
          <span className="text-break">{info.getValue()}</span>
        ),
        filterFn: "includesString",
      }
    ),
    columnHelper.accessor(
      (row: any) =>
        row.assignee
          ? `${row.assignee.firstname} ${row.assignee.lastname}`
          : "",
      {
        header: "Assignee",
        cell: (info: any) => (
          <span className="text-break">{info.getValue()}</span>
        ),
        filterFn: "includesString",
      }
    ),
    columnHelper.accessor("shortDescription", {
      header: "Subject",
      cell: (info) => info.getValue(),
      filterFn: "includesStringSensitive",
    }),
    columnHelper.accessor("AddToList", {
      id: "AddToList",
      header: () => "",
      cell: (info: any) => (
        <div className="d-flex justify-content-center align-items-center p-1  text-break">
          <Button
            variant="danger"
            onClick={() => handleShowAlert(info.row.getValue("id"))}
          >
            <i className="fa fa-trash me-1" /> Remove from list
          </Button>
        </div>
      ),
      enableSorting: false,
    }),
  ];

  const [data, setData] = useState(() => [...troubleTicketsByModule]);

  const [sorting, setSorting] = useState([
    {
      id: "id",
      desc: true,
    },
  ]);

  const [globalFilter, setGlobalFilter] = React.useState("");

  useEffect(() => {
    setData(troubleTicketsByModule);
  }, [troubleTicketsByModule]);

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

  return troubleTicketsByModule && troubleTicketsByModule.length > 0 ? (
    <Accordion defaultActiveKey="0">
      <Accordion.Item eventKey="0">
        <Accordion.Header className="ibox-title">
          <h5>TTS</h5>
        </Accordion.Header>
        <Accordion.Body className="ibox-content pt-0">
          <div
            className="input-group search-table  position-sticky py-3 bg-white mb-0"
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
              className="position-sticky"
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
              {table.getRowModel().rows.map(
                (row: any) =>
                  !row.original.isAddToUserStories && (
                    <tr key={row.id} className="ty-1">
                      {row.getVisibleCells().map((cell: any) => (
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
                  )
              )}
            </tbody>
          </Table>
          <hr />
        </Accordion.Body>
      </Accordion.Item>
    </Accordion>
  ) : (
    <div>No trouble tickets found</div>
  );
}

export default TroubleTicketsTable;
