import { describe, it } from "mocha";
import { expect } from "chai";
import {
  specificationButtonColorSelector,
  userStoriesSelector,
  userStoryNextStatusSelector,
  userStorySelector,
} from "../../../selectors/specification/userStoriesSelectors";

const emptyState: any = {
  specification: {
    userStory: {},
  },
};
describe("userStoriesSelector", () => {
  it("should get userStories from specification state", () => {
    const state: any = {
      specification: {
        userStories: {
          "/foo/1": { "@id": "/foo/1", id: 1, shortDescription: "foo" },
          "/bar/2": { "@id": "/bar/2", id: 2, shortDescription: "bar" },
        },
      },
    };

    expect(userStoriesSelector(emptyState)).to.deep.equal({});
    expect(userStoriesSelector.recomputations()).to.equal(1);
    expect(userStoriesSelector(state)).to.deep.equal({
      "/foo/1": { "@id": "/foo/1", id: 1, shortDescription: "foo" },
      "/bar/2": { "@id": "/bar/2", id: 2, shortDescription: "bar" },
    });
    expect(userStoriesSelector.recomputations()).to.equal(2);
    userStoriesSelector(state);
    expect(userStoriesSelector.recomputations()).to.equal(2);
  });
});

describe("userStorySelector", () => {
  it("should get userStory from specification state", () => {
    const state: any = {
      specification: {
        userStory: {
          "/foo/1": { "@id": "/foo/1", id: 1, shortDescription: "foo" },
        },
      },
    };

    expect(userStorySelector(emptyState)).to.deep.equal({});
    expect(userStorySelector.recomputations()).to.equal(1);
    expect(userStorySelector(state)).to.deep.equal({
      "/foo/1": { "@id": "/foo/1", id: 1, shortDescription: "foo" },
    });
    expect(userStorySelector.recomputations()).to.equal(2);
    userStorySelector(state);
    expect(userStorySelector.recomputations()).to.equal(2);
  });
});

describe("userStoryNextStatusSelector", () => {
  it("should return PLANNED for PENDING status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "PENDING",
        },
      },
    };
    const expected = {
      color: "primary",
      name: "PLANNED",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return DEVELOPMENT for PLANNED status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "PLANNED",
        },
      },
    };
    const expected = {
      color: "secondary",
      name: "DEVELOPMENT",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return VALIDATED for HAS BEEN EDITED status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "HAS BEEN EDITED",
        },
      },
    };
    const expected = {
      color: "secondary",
      name: "VALIDATED",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return TESTING for DEVELOPMENT status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "DEVELOPMENT",
        },
      },
    };
    const expected = {
      color: "warning",
      name: "TESTING",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return VALIDATED for TESTING status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "TESTING",
        },
      },
    };
    const expected = {
      color: "primary",
      name: "VALIDATED",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return VALIDATED for VALIDATED status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "VALIDATED",
        },
      },
    };
    const expected = {
      color: "info",
      name: "VALIDATED",
    };
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });

  it("should return an empty object for an unknown status", () => {
    const state: any = {
      specification: {
        userStory: {
          status: "UNKNOWN",
        },
      },
    };
    const expected = {};
    expect(userStoryNextStatusSelector(state)).to.deep.equal(expected);
  });
});

describe("specificationButtonColorSelector", () => {
  it('should return "info" for PRODUCTION status', () => {
    const state: any = {
      specification: {
        specificationStatus: "PRODUCTION",
      },
    };
    const expected = "info";
    expect(specificationButtonColorSelector(state)).to.equal(expected);
  });

  it('should return "warning" for any other status', () => {
    const state: any = {
      specification: {
        specificationStatus: "DEVELOPMENT",
      },
    };
    const expected = "warning";
    expect(specificationButtonColorSelector(state)).to.equal(expected);
  });

  it('should return "warning" for an unknown status', () => {
    const state: any = {
      specification: {
        specificationStatus: "UNKNOWN",
      },
    };
    const expected = "warning";
    expect(specificationButtonColorSelector(state)).to.equal(expected);
  });

  it('should return "warning" for an empty status', () => {
    const state: any = {
      specification: {
        specificationStatus: "",
      },
    };
    const expected = "warning";
    expect(specificationButtonColorSelector(state)).to.equal(expected);
  });
});
