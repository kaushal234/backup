import { IHydraCollection } from "./IHydraCollection";

export type IGetAllModuleResponse = IHydraCollection<IModule>;

interface IModule {
  "@id": string;
  "@type": string;
  specification: null;
  id: number | null;
  name: string | null;
  operationalOwner: IPeople | null;
  shortDescription: string | null;
  fullDescription: string | null;
  dmsProcedureId: null;
  dmsHelpId: null;
  legacyLoc: number | null;
  migrationCurrentStep: null;
  migrationEstimatedHours: number | null;
  migrated: boolean;
  disabledForTroubleTicket: boolean;
  status: string;
  department: null;
  notificationColor: null;
  keyUser: null;
  application: IApplication | null;
  misRelative: boolean;
  notifyOperationalOwner: boolean;
  notifyKeyUser: boolean;
  typeDefaultAssignees: Array<unknown>;
  legacyId: number | null;
}

interface IPeople {
  "@id": string;
  "@type": string;
  jobTitle: string | null;
  businessUnit: IBusinessUnit | null;
  position: IPosition | null;
  username: string | null;
  email: string;
  legacyId: number | null;
  photo: unknown | null;
  firstname: string | null;
  lastname: string | null;
}

interface IBusinessUnit {
  "@id": string;
  "@type": string;
  legacyId: number | null;
  id: number;
  name: string;
  domain: string | null;
  region: IRegion | null;
}

interface IRegion {
  "@id": string;
  "@type": string;
  legacyId: number | null;
}

interface IApplication {
  "@id": string;
  "@type": string;
  name: string | null;
  jiraProjectId: number | null;
  id: number | null;
}

interface IPosition {
  "@id": string;
  "@type": string;
  id: number;
  code: string | null;
  description: string;
  legacyId: number | null;
}
