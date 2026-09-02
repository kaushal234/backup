import { describe, it } from "mocha";
import { expect } from "chai";
import { call, put } from "redux-saga/effects";
import { autofill, startSubmit, stopSubmit } from "redux-form";
import { client } from "../../../store";
import { APICallSuccess, APICallFailed } from "../../../actions/genericActions";
import { fetchAPI, generateFormErrors } from "../../../utils/api";
import {
  callCreateSBSparePartsRequest,
  callCreateTOCSparePartsRequest,
  callEditSparePartsRequest,
  callFetchAirport,
  callFetchContact,
  callFetchDeliveryAddresses,
} from "../../../sagas/sparePartsRequest/sparePartsRequestSagas";
import {
  writeSBSparePartsRequest,
  writeTocSparePartsRequest,
} from "../../../actions/sparePartsRequest/sparePartsRequestActions";
import { fetchDeliveryAddresses } from "../../../actions/sparePartsRequest/deliveryAddressesActions";
import { SPR_FETCH_AIRPORT } from "../../../constants";
import { fetchAirport } from "../../../actions/apc/airportsActions";
import { fetchContact } from "../../../actions/extranetUser/extranetUserActions";

describe("sparePartsRequestSagas", () => {
  describe("callCreateTOCSparePartsRequest", () => {
    describe("Successful calls", () => {
      const generator = callCreateTOCSparePartsRequest(
        writeTocSparePartsRequest({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/parts/toc_spare_parts_requests", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("SPR_CREATE_TOC_SPARE_PARTS_REQUEST", {
              data: { id: 15 },
            })
          )
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateTOCSparePartsRequest(
        writeTocSparePartsRequest({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/parts/toc_spare_parts_requests", { foo: "bar" })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            "hydra:description": "ERREUR",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("SPR_CREATE_TOC_SPARE_PARTS_REQUEST", error.response)
          )
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then autofill form", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(put(autofill("form", "errorMessage", "ERREUR")));
      });
      it("should then stop form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callCreateSBSparePartsRequest", () => {
    describe("Successful calls", () => {
      const generator = callCreateSBSparePartsRequest(
        writeSBSparePartsRequest({ foo: "bar" }, "form", 1)
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/parts/sb_spare_parts_requests", { foo: "bar" })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("SPR_CREATE_SB_SPARE_PARTS_REQUEST", {
              data: { id: 15 },
              index: 1,
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "sparePartsRequests[1].submitted", true))
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callCreateSBSparePartsRequest(
        writeSBSparePartsRequest({ foo: "bar" }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.post, "/parts/sb_spare_parts_requests", { foo: "bar" })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            "hydra:description": "ERREUR",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(
            APICallFailed("SPR_CREATE_SB_SPARE_PARTS_REQUEST", error.response)
          )
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then autofill form", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(put(autofill("form", "errorMessage", "ERREUR")));
      });
      it("should then stop form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callEditSparePartsRequest", () => {
    describe("Successful calls", () => {
      const generator = callEditSparePartsRequest(
        writeTocSparePartsRequest({ foo: "bar", id: 15 }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/parts/toc_spare_parts_requests/15", {
            foo: "bar",
            id: 15,
          })
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { id: 15 } }).value).to.deep.equal(
          put(
            APICallSuccess("SPR_EDIT_TOC_SPARE_PARTS_REQUEST", {
              data: { id: 15 },
            })
          )
        );
      });
      it("should then stop form submission", () => {
        expect(generator.next({ form: "form" }).value).to.deep.equal(
          put(stopSubmit("form", {}))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
    describe("Failing calls", () => {
      const generator = callEditSparePartsRequest(
        writeTocSparePartsRequest({ foo: "bar", id: 15 }, "form")
      );
      it("should first initiate form submission", () => {
        expect(generator.next().value).to.deep.equal(put(startSubmit("form")));
      });
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(client.put, "/parts/toc_spare_parts_requests/15", {
            foo: "bar",
            id: 15,
          })
        );
      });
      const error = {
        response: {
          status: 400,
          data: {
            "hydra:description": "ERREUR",
          },
        },
      };
      it("should then dispatch a failed event", () => {
        expect(generator.throw(error).value).to.deep.equal(
          put(APICallFailed("SPR_EDIT_TOC_SPARE_PARTS_REQUEST", error.response))
        );
      });
      it("should then generate errors", () => {
        expect(generator.next().value).to.deep.equal(
          call(generateFormErrors, error)
        );
      });
      it("should then autofill form", () => {
        expect(
          generator.next({ _error: "Internal Server Error." }).value
        ).to.deep.equal(put(autofill("form", "errorMessage", "ERREUR")));
      });
      it("should then stop form submission", () => {
        expect(generator.next().value).to.deep.equal(
          put(stopSubmit("form", { _error: "Internal Server Error." }))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchDeliveryAddresses", () => {
    describe("Successful calls", () => {
      const generator = callFetchDeliveryAddresses(
        fetchDeliveryAddresses("/foo/1", ["/bar/1"])
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(
          call(
            fetchAPI,
            "parts/spare_parts_request_delivery_addresses?archived=0&airport=/foo/1&contact.extranetUserProfile.customer[]=/bar/1"
          )
        );
      });
      it("should then dispatch a success event", () => {
        expect(generator.next({ data: { foo: "bar" } }).value).to.deep.equal(
          put(
            APICallSuccess("SPR_FETCH_DELIVERY_ADDRESSES", {
              data: { foo: "bar" },
              discriminator: "discr",
            })
          )
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchAirport", () => {
    describe("Successful calls", () => {
      const generator = callFetchAirport(
        fetchAirport("/foo/1", "form", SPR_FETCH_AIRPORT)
      );
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/foo/1"));
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: {
              "@id": "/foo/1",
              code: "FRA",
              cityName: "Trouville",
              country: { "@id": "/bar/1", name: "France", isoCode2: "FR" },
            },
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SPR_FETCH_AIRPORT", {
              data: {
                "@id": "/foo/1",
                code: "FRA",
                cityName: "Trouville",
                country: { "@id": "/bar/1", name: "France", isoCode2: "FR" },
              },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill("form", "airport", {
              value: "/foo/1",
              label: "FRA - Trouville",
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(
            autofill("form", "country", {
              value: "/bar/1",
              label: "France",
              isoCode2: "FR",
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "city", "Trouville"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchContact", () => {
    describe("Successful calls", () => {
      const generator = callFetchContact(fetchContact("/foo/1", "form"));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/foo/1"));
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: {
              lastname: "ON",
              firstname: "Phara",
              phones: [{ number: "0802 63 63 63" }],
              extranetUserProfile: { customer: { name: "creole" } },
            },
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SPR_FETCH_CONTACT", {
              data: {
                lastname: "ON",
                firstname: "Phara",
                phones: [{ number: "0802 63 63 63" }],
                extranetUserProfile: { customer: { name: "creole" } },
              },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "lastname", "ON"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "firstname", "Phara"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "phone", "0802 63 63 63"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "company", "creole"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
  describe("callFetchContactWithIndex", () => {
    describe("Successful calls", () => {
      const generator = callFetchContact(fetchContact("/foo/1", "form", 1));
      it("should then fetch the API", () => {
        expect(generator.next().value).to.deep.equal(call(fetchAPI, "/foo/1"));
      });
      it("should then dispatch a success event", () => {
        expect(
          generator.next({
            data: {
              lastname: "ON",
              firstname: "Phara",
              phones: [{ number: "0802 63 63 63" }],
              extranetUserProfile: { customer: { name: "creole" } },
            },
          }).value
        ).to.deep.equal(
          put(
            APICallSuccess("SPR_FETCH_CONTACT", {
              data: {
                lastname: "ON",
                firstname: "Phara",
                phones: [{ number: "0802 63 63 63" }],
                extranetUserProfile: { customer: { name: "creole" } },
              },
            })
          )
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "sparePartsRequests[1].lastname", "ON"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "sparePartsRequests[1].firstname", "Phara"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "sparePartsRequests[1].phone", "0802 63 63 63"))
        );
      });
      it("should then autofill form", () => {
        expect(generator.next().value).to.deep.equal(
          put(autofill("form", "sparePartsRequests[1].company", "creole"))
        );
      });
      it("should then be finished", () => {
        expect(generator.next().done).to.be.true;
      });
    });
  });
});
