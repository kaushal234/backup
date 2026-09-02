import { describe, it } from "mocha";
import { expect } from "chai";
import Translator from "bazinga-translator";
import validate from "../../../../model/form/human_resources/event/validation";

describe("event validation", () => {
  const defaultErrors = {
    name: Translator.trans("events.validation.name"),
    country: Translator.trans("events.validation.country"),
    startedAt: Translator.trans("events.validation.started_at"),
    endedAt: Translator.trans("events.validation.ended_at"),
  };

  it("Should have default errors", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
  it("Should throw validation error when started date is after end date", () => {
    expect(
      validate({
        name: "foo",
        country: "bar",
        startedAt: "2099-01-01T05:00:00.000Z",
        endedAt: "2098-01-01T05:00:00.000Z",
      })
    ).to.deep.equal({
      endedAt: Translator.trans("events.validation.ended_at_before"),
    });
  });
  it("Should throw validation error when started date is in the past", () => {
    expect(
      validate({
        name: "foo",
        country: "bar",
        startedAt: "2019-01-01T05:00:00.000Z",
        endedAt: "2098-01-01T05:00:00.000Z",
      })
    ).to.deep.equal({
      startedAt: Translator.trans("events.validation.started_at_today"),
    });
  });
  it("Should throw default validation error when event is empty", () => {
    expect(validate({})).to.deep.equal(defaultErrors);
  });
});
