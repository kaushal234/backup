import { createSelector } from "reselect";
import moment from "moment";
import { RootState } from "../../store";

const getLogs = (state: RootState, iri: any) => {
  return state.activity.logs[iri];
};

const getComments = (state: RootState, iri: any) => {
  return state.activity.comments[iri];
};

const getActivitySelector = (activities: any) => {
  if (!activities) {
    return null;
  }

  return Object.values(activities).map((activity: any, index) => ({
    ...activity,
    createdAt: moment(activity.createdAt),
    updatedAt: moment(activity.updatedAt),
    index,
  }));
};

const isCommentCreationPending = (state: RootState, iri: any) => {
  return !!state.activity.pendingCommentCreations[iri];
};

const isResultFetched = (state: RootState, iri: any) => {
  return iri in state.activity.comments || iri in state.activity.logs;
};

export const getLogsMapping = createSelector([getLogs], getActivitySelector);

export const getCommentsMapping = createSelector(
  [getComments],
  getActivitySelector
);

export const isCreationPending = createSelector(
  [isCommentCreationPending],
  (pending) => pending
);

export const isActivityResultFetched = createSelector(
  [isResultFetched],
  (pending) => pending
);
