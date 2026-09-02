import { USER_FETCH_CONNECTED_USER, USER_FETCH_USERS } from "../../constants";

export function fetchConnectedUser(type = USER_FETCH_CONNECTED_USER) {
  return {
    type,
    payload: {
      request: {
        url: "/me",
      },
    },
  };
}

export function fetchUsersList() {
  return {
    type: USER_FETCH_USERS,
    payload: {
      request: {
        url: "/people?order[lastname]=asc&hidden=0&disabled=0&normalization_groups_override[]=people_list&pagination=false",
      },
    },
  };
}

export function fetchUsersAsyncList(
  search: any,
  excludedGroup = "",
  customFilter = ""
) {
  let url = `/people?order[lastname]=asc&hidden=0&disabled=0&normalization_groups_override[]=people_list&pagination=false&q=${search}`;
  if (excludedGroup !== "") {
    url += `&excludeGroup=${excludedGroup}`;
  }
  if (customFilter !== "") {
    url += `&${customFilter}`;
  }
  return {
    type: USER_FETCH_USERS,
    payload: {
      request: {
        url,
      },
    },
  };
}
