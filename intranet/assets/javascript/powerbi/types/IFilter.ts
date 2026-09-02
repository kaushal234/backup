export interface IFilter {
  $schema: string;
  target: ITarget;
  filterType: number;
  operator: string;
  values: Array<string>;
  requireSingleSelection: boolean;
  displaySettings?: IDisplaySettings;
}

interface ITarget {
  table: string;
  column: string;
}
interface IDisplaySettings {
  isHiddenInViewMode: boolean;
}
