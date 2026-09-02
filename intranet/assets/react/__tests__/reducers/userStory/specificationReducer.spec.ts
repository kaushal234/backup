import { describe, it } from "mocha";
import { expect } from "chai";
import userStoryReducer from "../../../reducers/userStory/specificationReducer";
import {
  FAILED,
  HIDE_ERROR_ALERT,
  HIDE_SUCCESS_ALERT,
  SPECIFICATION_GET,
  SUCCESS,
  USER_STORY_DELETE,
  USER_STORY_EDIT,
  USER_STORY_GET,
  USER_STORY_RESET,
  USER_STORY_UPDATE_STATUS,
  USER_STORY_WRITE,
} from "../../../constants";

describe("Specification reducer", () => {
  const initialState = {
    showLoading: true,
    showSuccess: false,
    showFailed: false,
    errorMessage: "",
    showUserStoryDetail: false,
    loadingUserStory: false,
    userStoryRefresh: false,
    specificationStatus: "",
    showList: false,
    userStories: {},
    userStory: {},
  };

  it("should return the initial state", () => {
    expect(userStoryReducer(undefined, { type: "@@INIT" })).to.deep.equal({
      ...initialState,
    });
  });

  const fakeFailedData = {
    data: {
      "hydra:description": "error API",
    },
  };

  it("should handle USER_STORY_WRITE + FAILED", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: USER_STORY_WRITE + FAILED, payload: fakeFailedData }
      )
    ).to.deep.equal({
      ...initialState,
      showFailed: true,
      errorMessage: "error API",
    });
  });

  it("should handle USER_STORY_EDIT + FAILED", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: USER_STORY_EDIT + FAILED, payload: fakeFailedData }
      )
    ).to.deep.equal({
      ...initialState,
      showFailed: true,
      errorMessage: "error API",
    });
  });

  it("should handle USER_STORY_GET + FAILED", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: USER_STORY_GET + FAILED, payload: fakeFailedData }
      )
    ).to.deep.equal({
      ...initialState,
      showFailed: true,
      errorMessage: "error API",
      userStoryRefresh: true,
      showList: true,
      loadingUserStory: false,
    });
  });

  it("should handle USER_STORY_DELETE + FAILED", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: USER_STORY_DELETE + FAILED, payload: fakeFailedData }
      )
    ).to.deep.equal({
      ...initialState,
      showFailed: true,
      errorMessage: "error API",
      userStoryRefresh: true,
      showList: true,
    });
  });

  const fakeUserStoryGet = {
    data: {
      foo: "bar",
    },
  };

  it("should handle USER_STORY_WRITE + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: USER_STORY_WRITE + SUCCESS,
          payload: fakeUserStoryGet,
        }
      )
    ).to.deep.equal({
      ...initialState,
      showSuccess: true,
      userStoryRefresh: true,
      showList: true,
      userStory: {
        foo: "bar",
      },
      showUserStoryDetail: true,
    });
  });

  it("should handle USER_STORY_EDIT + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: USER_STORY_EDIT + SUCCESS,
          payload: fakeUserStoryGet,
        }
      )
    ).to.deep.equal({
      ...initialState,
      showSuccess: true,
      userStoryRefresh: true,
      showList: true,
      userStory: {
        foo: "bar",
      },
      showUserStoryDetail: true,
    });
  });

  const fakeSpecificationGet = {
    data: {
      status: "foo",
      userStories: {
        userStory: {
          foo: "bar",
        },
      },
    },
  };

  it("should handle SPECIFICATION_GET + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: SPECIFICATION_GET + SUCCESS, payload: fakeSpecificationGet }
      )
    ).to.deep.equal({
      ...initialState,
      specificationStatus: "foo",
      userStories: {
        userStory: {
          foo: "bar",
        },
      },
      showLoading: false,
    });
  });

  it("should handle USER_STORY_GET + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        { type: USER_STORY_GET + SUCCESS, payload: fakeUserStoryGet }
      )
    ).to.deep.equal({
      ...initialState,
      loadingUserStory: false,
      userStory: {
        foo: "bar",
      },
      showUserStoryDetail: true,
    });
  });

  it("should handle USER_STORY_DELETE + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: USER_STORY_DELETE + SUCCESS,
        }
      )
    ).to.deep.equal({
      ...initialState,
      showUserStoryDetail: false,
      userStoryRefresh: true,
    });
  });

  it("should handle USER_STORY_UPDATE_STATUS + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: USER_STORY_UPDATE_STATUS + SUCCESS,
          payload: fakeUserStoryGet,
        }
      )
    ).to.deep.equal({
      ...initialState,
      userStory: {
        foo: "bar",
      },
      userStoryRefresh: true,
    });
  });

  it("should handle USER_STORY_RESET + SUCCESS", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: USER_STORY_RESET + SUCCESS,
        }
      )
    ).to.deep.equal({
      ...initialState,
      userStoryRefresh: false,
    });
  });

  it("should handle USER_STORY_ + HIDE_SUCCESS_ALERT", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: `USER_STORY_${HIDE_SUCCESS_ALERT}`,
        }
      )
    ).to.deep.equal({
      ...initialState,
      showSuccess: false,
    });
  });

  it("should handle USER_STORY_ + HIDE_ERROR_ALERT", () => {
    expect(
      userStoryReducer(
        { ...initialState },
        {
          type: `USER_STORY_${HIDE_ERROR_ALERT}`,
        }
      )
    ).to.deep.equal({
      ...initialState,
      showFailed: false,
    });
  });
});
