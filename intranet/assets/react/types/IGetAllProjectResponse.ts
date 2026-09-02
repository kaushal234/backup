import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProjectResponse = IHydraCollection<IProject>;

export interface IProject {
  "@id": string;
  "@type": string;
  name: string;
  businessUnit: IBusinessUnit | null;
  indicesFactor: string;
  projectManager: IPeople | null;
  misOwner: IPeople | null;
  createdAt: string;
  startedAt: string;
  confidential: boolean;
  module: IModule | null;
  teamsLink: string | null;
  status: string;
  id: number;
  phases: Array<IPhase>;
  tags: Array<IProjectTag>;
  activePhase: null;
  activePhaseEstimatedClosureAt: null;
  activePhaseRevisedClosureAt: null;
  dueDate: string | null;
  revisedDueDate: null;
  estimatedHours: number;
  revisedEstimatedHours: null;
}

interface IBusinessUnit {
  "@id": string;
  "@type": string;
  name: string;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  firstname: string | null;
  lastname: string | null;
}

interface IModule {
  "@id": string;
  "@type": string;
  name: string;
  operationalOwner: IPeople | null;
  keyUser: IPeople | null;
}

interface IPhase {
  "@id": string;
  "@type": string;
  number: number;
  estimatedClosureAt: string | null;
  revisedClosureAt: string | null;
  estimatedHours: number;
  revisedEstimatedHours: number | null;
  tasks: Array<unknown>;
}

interface IProjectTag {
  "@id": string;
  "@type": string;
  name: string;
}
