import { describe, it } from "mocha";
import { expect } from "chai";
import { takeLatest } from "redux-saga/effects";
import watchSagaForROLE from "../../../sagas/role/roleSagas";
import { ROLE_FETCH_ROLE } from "../../../constants";
import callGenericGetGenerator from "../../../sagas/common/generator";

describe("watchSagaForROLE", () => {
  it("should listen for ROLE_FETCH_ROLE", () => {
    const generator = watchSagaForROLE();
    expect(generator.next().value).to.deep.equal(
      takeLatest(ROLE_FETCH_ROLE, callGenericGetGenerator)
    );
  });

  it("should be done", () => {
    const generator = watchSagaForROLE();
    generator.next();
    expect(generator.next().done).to.equal(true);
  });
});
