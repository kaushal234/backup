import {
  USER_STORY_DELETE,
  USER_STORY_EDIT,
  USER_STORY_GET,
  USER_STORY_UPDATE_STATUS,
  USER_STORY_WRITE,
  SPECIFICATION_GET,
  USER_STORY_RESET,
  USER_STORY_DUPLICATE,
} from "../../constants";

export function writeUserStory(userStory: any, form: any) {
  let url = `/mis/user_stories`;
  let type = USER_STORY_WRITE;
  if (userStory.id) {
    url += `/${userStory.id}`;
    delete userStory.id;
    type = USER_STORY_EDIT;
  }
  return {
    type,
    payload: {
      url,
      body: userStory,
      form,
    },
  };
}

export function getSpecification(specificationId: any) {
  return {
    type: SPECIFICATION_GET,
    payload: {
      url: `/mis/specifications/${specificationId}`,
    },
  };
}

export function getUserStory(userStoryId: any) {
  return {
    type: USER_STORY_GET,
    payload: {
      url: `/mis/user_stories/${userStoryId}`,
    },
  };
}

export function deleteUserStory(userStoryId: any) {
  return {
    type: USER_STORY_DELETE,
    payload: {
      url: `/mis/user_stories/${userStoryId}`,
    },
  };
}

export function getUserStoryReset() {
  return {
    type: USER_STORY_RESET,
  };
}

export function updateStatusUserStory(userStoryId: any, status: any) {
  let body = {};
  switch (status) {
    case "PENDING":
      body = {
        status: "PLANNED",
      };
      break;
    case "PLANNED":
      body = {
        status: "DEVELOPMENT",
      };
      break;
    case "EDIT":
      body = {
        status: "HAS BEEN EDITED",
      };
      break;
    case "HAS BEEN EDITED":
      body = {
        status: "VALIDATED",
      };
      break;
    case "DEVELOPMENT":
      body = {
        status: "TESTING",
      };
      break;
    case "TESTING":
      body = {
        status: "VALIDATED",
      };
      break;
    default:
      break;
  }
  return {
    type: USER_STORY_UPDATE_STATUS,
    payload: {
      url: `mis/user_stories/${userStoryId}/status`,
      body,
    },
  };
}

export function duplicateUserStory(userStoryId: any) {
  return {
    type: USER_STORY_DUPLICATE,
    payload: {
      url: `mis/user_stories/${userStoryId}/duplicate`,
      body: {},
    },
  };
}
