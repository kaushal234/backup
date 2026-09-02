import { TAG_FETCH_LIST } from "../constants";

export function getTags(discriminator: any = null, apiRoutePrefix: any = null) {
  let url = discriminator ? `/${discriminator}_tags` : `/tags`;
  if (apiRoutePrefix) {
    url = `/${apiRoutePrefix}${url}`;
  }

  return {
    type: TAG_FETCH_LIST,
    payload: {
      url,
    },
  };
}
