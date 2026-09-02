export interface IError {
  status?: number;
  statusText?: string;
  headers?: {
    get: (header: string) => string | undefined;
  };
  error_description?: string;
  error?: unknown;
}
