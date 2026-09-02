export interface IGanttChartData {
  start: number | string;
  end: number | string;
  completed?: ICompleted;
  name: IName;
  label?: string;
}

interface ICompleted {
  amount?: number;
  fill?: string;
}

export interface IName {
  lines: Array<string>;
  link: string;
}
