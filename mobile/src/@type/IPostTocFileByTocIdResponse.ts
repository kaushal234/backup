export type IPostTocFileByTocIdResponse = ITechnicianOnCallFile;

export interface ITechnicianOnCallFile {
  "@context": string;
  "@id": string;
  "@type": string;
  public: boolean;
  id: number;
  filePath: string;
  poster: IPeople | null;
  createdAt: string;
  description: string | null;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
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
