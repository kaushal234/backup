import React from "react";
import Translator from "bazinga-translator";
import { Table } from "react-bootstrap";
import { useAppSelector } from "../../hooks/hooks";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import "./TocStatusParts.css";

function TocStatusParts() {
  const parts = useAppSelector((state) => state.tocDetail.data?.parts) ?? [];
  const sparePartsRequests =
    useAppSelector((state) => state.tocDetail.data?.sparePartsRequests) ?? [];

  const totalSparePartsRequestsParts = sparePartsRequests.reduce(
    (total, sparePartsRequest) => total + sparePartsRequest.parts.length,
    0
  );

  return (
    <Table className="toc_status_parts__wrapper" hover responsive>
      <thead>
        <tr>
          <th>{Translator.trans("toc.fields.parts.part_number")}</th>
          <th>{Translator.trans("toc.fields.parts.vendor_part_number")}</th>
          <th className="description-size">
            {Translator.trans("toc.fields.parts.description")}
          </th>
          <th>{Translator.trans("toc.fields.parts.defective")}</th>
        </tr>
      </thead>
      <tbody>
        {parts.map((part) => {
          return (
            <tr key={part.id}>
              <td>{part.partNumber}</td>
              <td>{part.vendorPartNumber}</td>
              <td
                // eslint-disable-next-line react/no-danger
                dangerouslySetInnerHTML={{
                  __html: part.description ?? "",
                }}
              />
              <td>
                <GenericFormComponent
                  type="Checkbox"
                  name={`defectiveParts.${part["@id"]}`}
                />
              </td>
            </tr>
          );
        })}
        {sparePartsRequests.map((sparePartsRequest) =>
          sparePartsRequest.parts.map((part) => (
            <tr key={part.id}>
              <td>{part.partNumber}</td>
              <td />
              <td
                // eslint-disable-next-line react/no-danger
                dangerouslySetInnerHTML={{
                  __html: part.description ?? "",
                }}
              />
              <td>
                <GenericFormComponent
                  type="Checkbox"
                  name={`defectiveParts.${part["@id"]}`}
                />
              </td>
            </tr>
          ))
        )}
        {parts.length === 0 && totalSparePartsRequestsParts === 0 && (
          <tr>
            <td colSpan={15}>
              {Translator.trans("toc.status.modal.no_parts")}
            </td>
          </tr>
        )}
      </tbody>
    </Table>
  );
}

export default TocStatusParts;
