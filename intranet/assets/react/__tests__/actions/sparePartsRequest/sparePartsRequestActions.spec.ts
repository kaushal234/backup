import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../../actions/sparePartsRequest/sparePartsRequestActions";

describe("sparePartsRequestActions", () => {
  describe("writeTocSparePartsRequest", () => {
    it("should create a create spare parts request action", () => {
      const expectedAction = {
        type: "SPR_CREATE_TOC_SPARE_PARTS_REQUEST",
        payload: {
          url: "/parts/toc_spare_parts_requests",
          body: { foo: "bar" },
          form: "foo_form",
        },
      };
      expect(
        actions.writeTocSparePartsRequest({ foo: "bar" }, "foo_form")
      ).to.deep.equal(expectedAction);
    });
  });
  describe("writeTocSparePartsRequest", () => {
    it("should create an edit spare parts request action", () => {
      const expectedAction = {
        type: "SPR_EDIT_TOC_SPARE_PARTS_REQUEST",
        payload: {
          url: "/parts/toc_spare_parts_requests/1",
          body: { foo: "bar", id: 1 },
          form: "foo_form",
        },
      };
      expect(
        actions.writeTocSparePartsRequest({ foo: "bar", id: 1 }, "foo_form")
      ).to.deep.equal(expectedAction);
    });
  });

  describe("writeSBSparePartsRequest", () => {
    it("should create a create spare parts request action", () => {
      const expectedAction = {
        type: "SPR_CREATE_SB_SPARE_PARTS_REQUEST",
        index: 1,
        payload: {
          url: "/parts/sb_spare_parts_requests",
          body: { foo: "bar" },
          form: "foo_form",
        },
      };
      expect(
        actions.writeSBSparePartsRequest({ foo: "bar" }, "foo_form", 1)
      ).to.deep.equal(expectedAction);
    });
  });
});
