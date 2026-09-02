import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCommentResponse = IHydraCollection<IComment>;

export interface IComment {
  "@id": string;
  "@type": string;
  id: number | null;
  message: string;
  files: Array<ICommentFile>;
  metadata: IMetadata | null;
  discriminator: string | null;
  legacyId: number | null;
  resource: string | null;
  user: IPeople | null;
  createdAt: string;
  updatedAt: string;
  public: boolean;
  position: number | null;
}

export interface ICommentFile {
  "@id": string;
  "@type": string;
  id: number;
  filePath: string;
  createdAt: string;
  mimeType: string;
  description: string | null;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}

interface IMetadata {
  translation: string | null;
  factoryFlag?: string;
  confidential?: boolean;
}

export type IGetAllCommentRawResponse = IHydraCollection<IRawComment>;

interface IRawComment extends Omit<IComment, "metadata"> {
  metadata: Array<unknown> | IMetadata | null;
}
