import { createSelector } from "reselect";
import _ from "lodash";
import { RootState } from "../../store";

const getExtranetUsers = (state: RootState, discriminator = null) => {
  return discriminator !== null
    ? state.extranetUser.extranetUsers[discriminator]
    : state.extranetUser.extranetUsers;
};

export const buildOptionLabel = (extranetUser: any) => {
  const USER_TAGS = {
    asBuyer: "B",
    asUser: "U",
    asMaintainer: "M",
  };

  const baseLabel = `${extranetUser.lastname} ${extranetUser.firstname}`;
  const extranetUserIdString = extranetUser.id
    ? ` (ID#${extranetUser.id})`
    : "";
  const relationsTags = Object.entries(USER_TAGS)
    .map(([key, tag]) => (extranetUser[key] ? tag : ""))
    .filter((tag) => tag !== "")
    .join(" - ");
  const relationsTagsLabel = relationsTags ? ` (${relationsTags})` : "";

  return `${baseLabel}${extranetUserIdString}${relationsTagsLabel}`.trim();
};

export const getExtranetUsersMapping = createSelector(
  [getExtranetUsers],
  (extranetUsers) => {
    if (!extranetUsers) {
      return [];
    }
    return Object.values(extranetUsers).map((extranetUser: any) => ({
      value: extranetUser["@id"],
      label: `${_.get(extranetUser, "lastname")} ${_.get(
        extranetUser,
        "firstname"
      )} (ID#${_.get(extranetUser, "id")})`,
    }));
  }
);
