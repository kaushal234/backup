import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/equipmentRecord/equipmentRecordReducer";

const initialState = {
  equipmentRecords: [],
  updatedLinesSuccess: [],
  showError: false,
  errorMessage: "",
};

describe("equipmentRecordReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });
  it("should return the actual state", () => {
    expect(reducer(initialState, { type: "NOT_EXISTING" })).to.deep.equal(
      initialState
    );
  });
  it("should handle API_FETCH_ER", () => {
    expect(
      reducer(initialState, {
        type: "API_FETCH_ER",
        payload: { iri: "/resources/42" },
      })
    ).to.deep.equal({
      equipmentRecords: [],
      equipmentRecordsListIsLoading: true,
      updatedLinesSuccess: [],
      showError: false,
      errorMessage: "",
    });
  });
  it("should handle API_FETCH_ER_SUCCESS", () => {
    expect(
      reducer(initialState, {
        type: "API_FETCH_ER_SUCCESS",
        payload: {
          iri: "/resources/42",
          data: {
            "hydra:member": [
              { "@id": "/equipment_records/69", serialNumber: "SERIAL" },
              { "@id": "/equipment_records/70", serialNumber: "NUMBER" },
            ],
          },
        },
      })
    ).to.deep.equal({
      equipmentRecords: {
        "/equipment_records/69": {
          "@id": "/equipment_records/69",
          serialNumber: "SERIAL",
        },
        "/equipment_records/70": {
          "@id": "/equipment_records/70",
          serialNumber: "NUMBER",
        },
      },
      equipmentRecordsListIsLoading: false,
      updatedLinesSuccess: [],
      showError: false,
      errorMessage: "",
    });
  });

  it("should handle API_UPDATE_ER_FAILED", () => {
    const action = {
      type: "API_UPDATE_ER_FAILED",
      payload: {
        data: {
          "hydra:description": "An error occurred",
        },
      },
    };

    const newState = reducer(initialState, action);

    expect(newState.showError).to.be.true;
    expect(newState.errorMessage).to.equal("An error occurred");
  });
  it("should add the equipment IRI to updatedLinesSuccess", () => {
    const newInitialState: any = {
      equipmentRecords: {},
      updatedLinesSuccess: [],
      showError: true,
    };

    const action = {
      type: "API_UPDATE_ER_SUCCESS",
      payload: {
        data: {
          "@id": "/equipment_records_odp/1",
          id: 123,
          name: "Equipment A",
        },
      },
    };

    const newState = reducer(newInitialState, action);

    expect(newState.updatedLinesSuccess[0]).to.match(/^\/equipment_records\//);
    expect(newState.updatedLinesSuccess[0]).to.equal("/equipment_records/123");
    expect(newState.showError).to.be.false;
  });
});
