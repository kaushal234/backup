import {
  EXTRANET_USER_FETCH_EXTRANET_USERS,
  SPR_FETCH_CONTACT,
} from "../../constants";

export function fetchExtranetUsersList(search: any) {
  return {
    type: EXTRANET_USER_FETCH_EXTRANET_USERS,
    payload: {
      request: {
        url: `/sales/extranet_users?order[lastname]=asc&hidden=0&extranetUserProfile.archived=0&normalization_groups_override[]=extranet_user_list&pagination=false&q=${search}`,
      },
    },
  };
}

export function fetchExtranetUsersHierarchyList(customer: any) {
  return {
    type: EXTRANET_USER_FETCH_EXTRANET_USERS,
    payload: {
      request: {
        url: `/sales/extranet_users?customer_hierarchy=${customer}`,
      },
    },
  };
}

export function fetchContact(iri: any, form: any, index: any = null) {
  return {
    form,
    index,
    type: SPR_FETCH_CONTACT,
    payload: {
      request: {
        url: iri,
      },
    },
  };
}

export function fetchExtranetUsersByCustomer(customerIri: string) {
  return {
    type: EXTRANET_USER_FETCH_EXTRANET_USERS,
    payload: {
      request: {
        url: `/sales/extranet_users?relatedToCustomer=${customerIri}`,
      },
    },
  };
}
