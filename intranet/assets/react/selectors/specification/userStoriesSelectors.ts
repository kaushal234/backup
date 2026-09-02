import Translator from "bazinga-translator";
import { RootState } from "../../store";

export const userStoriesSelector: any = (state: RootState) => {
  return state.specification ? state.specification.userStories : {};
};

export const userStorySelector: any = (state: RootState) => {
  return state.specification ? state.specification.userStory : {};
};

export const userStoryNextStatusSelector = (state: RootState) => {
  let userStoryNextStatus = {};
  switch (state.specification.userStory.status) {
    case "PENDING":
      userStoryNextStatus = {
        color: "primary",
        name: Translator.trans("mis.specification.planned"),
      };
      break;
    case "PLANNED":
      userStoryNextStatus = {
        color: "secondary",
        name: Translator.trans("mis.specification.development"),
      };
      break;
    case "HAS BEEN EDITED":
      userStoryNextStatus = {
        color: "secondary",
        name: Translator.trans("mis.specification.validated"),
      };
      break;
    case "DEVELOPMENT":
      userStoryNextStatus = {
        color: "warning",
        name: Translator.trans("mis.specification.testing"),
      };
      break;
    case "TESTING":
      userStoryNextStatus = {
        color: "primary",
        name: Translator.trans("mis.specification.validated"),
      };
      break;
    case "VALIDATED":
      userStoryNextStatus = {
        color: "info",
        name: Translator.trans("mis.specification.validated"),
      };
      break;
    default:
      break;
  }
  return userStoryNextStatus;
};

export const specificationButtonColorSelector = (state: RootState) => {
  let specificationButtonColor = "";
  switch (state.specification.specificationStatus) {
    case "PRODUCTION":
      specificationButtonColor = "#399a4b";
      break;
    default:
      specificationButtonColor = "#f7a54a";
      break;
  }
  return specificationButtonColor;
};
