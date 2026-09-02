export type IPostCommentByIdResponse = IComment;

export interface IComment {
  "@context": string;
  "@id": string;
  "@type": string;
  message: string;
  files: Array<ICommentFile>;
  metadata: Array<unknown>;
  discriminator: string | null;
  legacyId: number | null;
  resource: string | null;
  user: IPeople | null;
  createdAt: string;
  updatedAt: string;
  public: boolean;
}

export interface ICommentFile {
  "@id": string;
  "@type": string;
  id: number;
  filePath: string;
  createdAt: string;
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
