import React from "react";
import SparePartsRequestItem from "./SparePartsRequestItem";

interface IProps {
  fields: any;
}

const renderSparePartsRequestForm = ({ fields }: IProps) => {
  return (
    <div className="row">
      {fields.map((spr: any, index: number) => (
        <div className="col-md-12" key={index}>
          <div className="card ">
            <div className="card-header">
              <h4>SPR #{index + 1}</h4>
            </div>
            {!fields.get(index).submitted && (
              <SparePartsRequestItem
                formKey={spr}
                sparePartsRequest={fields.get(index)}
                index={index}
              />
            )}

            {fields.get(index).submitted && (
              <div className="text-center">
                <h1 style={{ color: "green" }}>
                  <i className="fa fa-fw fa-check" />
                </h1>
                <h1>SAVED</h1>
              </div>
            )}
          </div>
        </div>
      ))}
    </div>
  );
};

export default renderSparePartsRequestForm;
