import React from "react";
import Col from "react-bootstrap/Col";
import Row from "react-bootstrap/Row";
import SPRPartItem from "../SparePartsRequest/PartItem";
import NCRPartItem from "../NonConformity/PartItem";
import SCARPartItem from "../SupplierCorrectiveActionRequest/PartItem";
import VWCPartItem from "../VendorWarrantyClaim/PartItem";

interface IProps {
  fields: any;
  location: any;
  module: any;
}

const renderPartsForm = ({ fields, location, module }: IProps) => {
  const parts = fields.getAll();
  const objectToPush = { module };
  return (
    <div>
      {parts.length < 10 && (
        <div>
          <button
            className="btn btn-info"
            type="button"
            onClick={() => fields.push(objectToPush)}
          >
            <i className="fa fa-fw fa-plus" />
            &nbsp;Add
          </button>
        </div>
      )}
      <Row>
        {fields.map((part: any, index: number) => (
          <Col xl={6} key={index}>
            <div className="card mb-4">
              <div className="card-header">
                <button
                  className="btn btn-sm btn-danger float-end"
                  type="button"
                  title="Remove Part"
                  onClick={() => fields.remove(index)}
                >
                  <i className="fa fa-fw fa-trash" />
                </button>
                <h4>Part #{index + 1}</h4>
              </div>
              {module === "SPR" && <SPRPartItem index={index} part={part} />}
              {module === "NCR" && (
                <NCRPartItem index={index} part={part} erp={location.erp} />
              )}
              {module === "SCAR" && (
                <SCARPartItem part={part} erp={location.erp} />
              )}
              {module === "VWC" && (
                <VWCPartItem index={index} part={part} erp={location.erp} />
              )}
            </div>
          </Col>
        ))}
      </Row>
    </div>
  );
};

export default renderPartsForm;
