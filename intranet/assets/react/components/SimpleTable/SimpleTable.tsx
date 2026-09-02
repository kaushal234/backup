import React from "react";
import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Paper from "@mui/material/Paper";
import "./SimpleTable.css";
import Box from "@mui/material/Box";
import Translator from "bazinga-translator";

interface IProps {
  headers: Array<{ minWidth?: string; value: string; className?: string }>;
  rows: Array<Array<React.ReactNode | string>>;
  isOneLiner?: boolean;
  dataCy?: string;
  wrapHeader?: boolean;
  noCard?: boolean;
  noContentMessage?: string;
}

export default function SimpleTable(props: IProps) {
  const {
    headers,
    rows,
    isOneLiner,
    dataCy,
    wrapHeader,
    noCard,
    noContentMessage,
  } = props;

  return (
    <TableContainer
      className={`simple_table__wrapper ${
        isOneLiner && "simple_table__wrapper--one_line"
      }`}
      component={noCard ? Box : Paper}
    >
      <Table stickyHeader data-cy={dataCy}>
        <TableHead>
          <TableRow data-cy={`${dataCy}-header`}>
            {headers.map((header, idx) => (
              <TableCell
                className={`${wrapHeader && "simple_table__no_wrap"}`}
                key={header.value}
                sx={{
                  minWidth: header.minWidth ?? "120px",
                }}
                data-cy={`${dataCy}-cell-${idx}`}
              >
                {Translator.trans(header.value)}
              </TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {rows.map((row, idx) => (
            <TableRow
              data-cy={`${dataCy}-row-${idx}`}
              // eslint-disable-next-line react/no-array-index-key
              key={idx}
              sx={{ "&:last-child td, &:last-child th": { border: 0 } }}
            >
              {row.map((cell, cellIdx) => (
                <TableCell
                  data-cy={`${dataCy}-cell-${cellIdx}`}
                  // eslint-disable-next-line react/no-array-index-key
                  key={`${cellIdx}-${cell}`}
                  className={`${headers[cellIdx].className} ${
                    isOneLiner && "cui_one_line"
                  }`}
                  sx={{
                    width: headers[cellIdx].minWidth,
                    maxWidth: headers[cellIdx].minWidth,
                  }}
                >
                  {cell || "---"}
                </TableCell>
              ))}
            </TableRow>
          ))}
          {rows.length === 0 && noContentMessage && (
            <TableRow
              data-cy={`${dataCy}-row-no-content`}
              sx={{ "&:last-child td, &:last-child th": { border: 0 } }}
            >
              <TableCell
                data-cy={`${dataCy}-cell-no-content`}
                colSpan={headers.length}
              >
                {Translator.trans(noContentMessage ?? "")}
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </TableContainer>
  );
}
