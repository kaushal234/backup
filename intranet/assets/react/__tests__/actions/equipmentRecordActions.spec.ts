import { describe, it } from "mocha";
import { expect } from "chai";
import * as actions from "../../actions/equipmentRecordsActions";

describe("equipmentRecordsActions", () => {
  describe("fetchEquipmentRecordsBySerialNumber", () => {
    it("should create a fetch equipment records without param", () => {
      const expectedAction = {
        type: "API_FETCH_ER",
        payload: {
          request: {
            url: "/equipment_records",
          },
        },
      };
      expect(actions.fetchEquipmentRecords()).to.deep.equal(expectedAction);
    });

    it("should create a fetch equipment records action with 1 Serial Number", () => {
      const expectedAction = {
        type: "API_FETCH_ER",
        payload: {
          request: {
            url: "/equipment_records?serialNumber=galactica",
          },
        },
      };
      expect(actions.fetchEquipmentRecords("galactica")).to.deep.equal(
        expectedAction
      );
    });

    it("should create a fetch equipment records action with groups override", () => {
      const expectedAction = {
        type: "API_FETCH_ER",
        payload: {
          request: {
            url: "/equipment_records?serialNumber=YT-1300-FP-FM&normalization_groups_override[]=group1&normalization_groups_override[]=group2",
          },
        },
      };
      expect(
        actions.fetchEquipmentRecordsBySerialNumber(
          "YT-1300-FP-FM",
          ["group1", "group2"],
          true
        )
      ).to.deep.equal(expectedAction);
    });

    it("should handle multiple serial numbers", () => {
      const expectedAction = {
        type: "API_FETCH_ER",
        payload: {
          request: {
            url: "/equipment_records?serialNumber[]=YT-1300-FP-FM&serialNumber[]=VCX-100-Ghost",
          },
        },
      };
      expect(
        actions.fetchEquipmentRecordsBySerialNumber(
          "YT-1300-FP-FM, VCX-100-Ghost"
        )
      ).to.deep.equal(expectedAction);
    });
  });

  describe("updateEquipmentRecord", () => {
    it("should create an update equipment record action", () => {
      const equipmentRecord = {
        "@id": "/equipment_records/1",
        serialNumber: "JustForTest",
      };
      const expectedAction = {
        type: "API_UPDATE_ER",
        payload: {
          request: {
            url: "/equipment_records/1",
            body: equipmentRecord,
          },
        },
        index: 0,
      };
      expect(
        actions.updateEquipmentRecord(
          equipmentRecord,
          "/equipment_records/1",
          0
        )
      ).to.deep.equal(expectedAction);
    });
  });
});
