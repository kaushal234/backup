import {
  MIS_CREATE_SUPPORT_TEAM,
  MIS_UPDATE_SUPPORT_TEAM,
} from "../../constants";

export function addSupportTeam(payload: any) {
  return {
    type: MIS_CREATE_SUPPORT_TEAM,
    payload: {
      url: "/mis/support_teams",
      body: payload,
      form: "support_team_form",
    },
  };
}

export function updateSupportTeam(payload: any) {
  return {
    type: MIS_UPDATE_SUPPORT_TEAM,
    payload: {
      url: `/mis/support_teams/${payload.id}`,
      body: payload,
      form: "support_team_form",
    },
  };
}
