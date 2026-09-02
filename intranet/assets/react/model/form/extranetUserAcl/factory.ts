import _ from "lodash";

const extranetUserAclFactory = (values: any, index: any) => {
  return {
    crt: `/sales/customer_relationship_teams/${values.id}`,
    extranetUser: _.get(values.extranetUser, "value", null),
    extranetUserGroup: _.get(values.extranetUserGroups[index], "value", null),
    cDel: values.deliveryAddress,
  };
};

export { extranetUserAclFactory };
