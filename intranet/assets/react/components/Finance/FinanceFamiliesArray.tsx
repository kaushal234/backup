import React from "react";
import FinanceFamilyLine from "./FinanceFamilyLine";

interface IProps {
  fields: any;
  factories: any;
  sso: any;
  isGrantedCreate: any;
}

function FinanceFamiliesArray({
  fields,
  factories,
  sso,
  isGrantedCreate,
}: IProps) {
  return (
    <div>
      <table className="table matrix-table">
        <thead>
          <tr>
            <th className="top-label" />
            {factories.map((factory: any, index: number) => {
              return (
                <th key={index} className="top-label">
                  {factory.label}
                </th>
              );
            })}
          </tr>
        </thead>
        <tbody>
          {fields.map((financeFamily: any, index: number) => {
            return (
              <FinanceFamilyLine
                key={index}
                sso={sso}
                financeFamilyIndex={index}
                factories={factories}
                fields={fields}
                financeFamily={fields.get(index)}
                financeFamilyPath={financeFamily}
              />
            );
          })}
        </tbody>
      </table>
      {isGrantedCreate && (
        <button
          className="btn btn-info"
          type="button"
          onClick={() => fields.push({ factories: [], name: "", pricings: {} })}
        >
          <i className="fa fa-fw fa-plus" />
        </button>
      )}
    </div>
  );
}

export default FinanceFamiliesArray;
