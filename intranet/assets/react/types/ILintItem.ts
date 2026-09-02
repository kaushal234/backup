import { IMessage } from "./IMessage";

export interface ILintItem {
  filePath: string;
  messages: Array<IMessage>;
  errorCount: number;
  fatalErrorCount: number;
  warningCount: number;
  fixableErrorCount: number;
  fixableWarningCount: number;
}
