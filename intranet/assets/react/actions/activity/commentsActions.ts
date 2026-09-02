import {
  ACTIVITY_CREATE_COMMENT,
  ACTIVITY_FETCH_COMMENTS,
} from "../../constants";

export function createComment(
  resourceIri: string,
  message: string,
  file?: any | null,
  isPublic = true,
  metadata: Record<string, any> = {}
) {
  return {
    type: ACTIVITY_CREATE_COMMENT,
    payload: {
      request: {
        url: "/comments",
        body: {
          resource: resourceIri,
          message,
          file,
          public: isPublic,
          metadata,
        },
      },
    },
  };
}

export function getComments(iri: any) {
  return {
    type: ACTIVITY_FETCH_COMMENTS,
    payload: {
      url: `/comments?normalization_groups[]=file:light&normalizationGroups[]=activity_position&resource=${iri}&pagination=false&extraComment=true`,
      iri,
    },
  };
}
