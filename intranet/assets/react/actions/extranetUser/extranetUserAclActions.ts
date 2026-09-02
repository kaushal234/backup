import { EXTRANET_USER_CREATE_EXTRANET_USER_ACLS } from "../../constants";

export function writeExtranetUserAcl(extranetUserAcl: any, form: any) {
  return {
    type: EXTRANET_USER_CREATE_EXTRANET_USER_ACLS,
    payload: {
      url: `/sales/extranet_user_acls`,
      form,
      body: extranetUserAcl,
    },
  };
}
