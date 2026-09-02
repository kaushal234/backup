import { describe, it } from "mocha";
import { expect } from "chai";
import { eventFactory } from "../../../../../model/form/human_resources/event/factory";

const dummyEvent = {
  name: "foo",
  id: 1,
  country: { value: "/countries/1" },
  state: "foo state",
  startedAt: "2019-01-01T05:00:00.000Z",
  endedAt: "2020-01-01T05:00:00.000Z",
  dayOff: true,
};
describe("Event add factory", () => {
  it("should return an Event", () => {
    expect(eventFactory(dummyEvent)).to.deep.equal({
      name: "foo",
      id: 1,
      country: "/countries/1",
      state: "foo state",
      startedAt: "2019-01-01T00:00:00-05:00",
      endedAt: "2020-01-01T00:00:00-05:00",
      dayOff: true,
    });
  });
});
