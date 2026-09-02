import {
  SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM,
  SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM,
} from "../../constants";

export function writeCustomerRelationshipTeam(
  customerRelationshipTeam: any,
  form: any
) {
  const type = customerRelationshipTeam.id
    ? SALES_EDIT_CUSTOMER_RELATIONSHIP_TEAM
    : SALES_CREATE_CUSTOMER_RELATIONSHIP_TEAM;
  const url = customerRelationshipTeam.id
    ? `/sales/customer_relationship_teams/${customerRelationshipTeam.id}`
    : "/sales/customer_relationship_teams";
  return {
    type,
    payload: {
      url,
      form,
      body: customerRelationshipTeam,
    },
  };
}
