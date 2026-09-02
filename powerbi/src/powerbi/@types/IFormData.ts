interface IRequestData {
  id: string;
}

export interface IFormData {
  reports: Array<IRequestData>;
  datasets?: Array<IRequestData>;
  targetWorkspaces?: Array<IRequestData>;
}
