import { describe, it } from "mocha";
import { expect } from "chai";
import reducer from "../../../reducers/role/roleReducer";

const initialState = {
  role: [],
  roleListIsLoading: false,
};

describe("roleReducer", () => {
  it("should return the initial state", () => {
    expect(reducer(undefined, { type: "@@INIT" })).to.deep.equal(initialState);
  });

  it("should handle ROLE_FETCH_ROLE_SUCCESS", () => {
    const action = {
      type: "ROLE_FETCH_ROLE_SUCCESS",
      payload: {
        data: {
          "hydra:member": [{ "@id": "/group/1", name: "ROLE_MKU" }],
        },
      },
    };

    const expectedState = {
      role: {
        "/group/1": { "@id": "/group/1", name: "ROLE_MKU" },
      },
      roleListIsLoading: false,
    };

    expect(reducer(initialState, action)).to.deep.equal(expectedState);
  });
});
