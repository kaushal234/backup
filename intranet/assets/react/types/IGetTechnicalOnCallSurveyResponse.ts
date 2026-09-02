export type IGetTechnicalOnCallSurveyResponse = ITechnicianOnCallSurvey;

export interface ITechnicianOnCallSurvey {
  "@context": string;
  "@id": string;
  "@type": string;
  execution: number;
  responsiveness: number;
  communication: number;
  attitude: number;
  comment: string;
  createdBy: IPeople | null;
  createdAt: string;
  id: number | null;
  technicianOnCall: ITechnicianOnCall;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  photo: string | null;
  firstname: string | null;
  lastname: string | null;
}

interface ITechnicianOnCall {
  "@id": string;
  "@type": string;
  confidential: boolean;
  title: string;
  originalTitle: string | null;
  description: string;
  originalDescription: string | null;
  status: string;
  createdAt: string;
  updatedAt: string | null;
  createdBy: string;
  equipmentRecord: string;
  assignee: string;
  unitOperationalStatus: string;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  airport: string;
  salesOrganisationService: string;
  factoryFlag: boolean;
  customer: string;
  id: number;
  tags: Array<string>;
  mainContact: string;
  mainFile: string | null;
  openDays: number;
  daysWithoutActivity: number;
  daysWithoutActivityStatus: string;
}
