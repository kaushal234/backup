import { ROLE_FETCH_ROLE } from "../../constants";

export function fetchROLE(search: any) {
  return {
    type: ROLE_FETCH_ROLE,
    payload: {
      request: {
        url: `/groups?name=${search}`,
      },
    },
  };
}
