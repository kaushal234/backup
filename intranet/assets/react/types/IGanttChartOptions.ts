import { IGanttChartData } from "./IGanttChartData";

export interface IGanttChartOptions {
  chart: IChart;
  title: ITitle;
  yAxis: IYAxis;
  navigator: INavigator;
  scrollbar: IScrollbar;
  rangeSelector: IRangeSelector;
  accessibility: IAccessibility;
  series: Array<ISeries>;
  tooltip: ITooltip;
}

interface IChart {
  type: string;
  height?: number;
  events?: IEvent;
}

interface IYAxis {
  uniqueNames: boolean;
  labels: IYAxisLabel;
}

interface INavigator {
  enabled: boolean;
  liveRedraw: boolean;
  series: INavigatorSeries;
  yAxis: INavigatorYAxis;
}

interface INavigatorYAxis {
  min: number;
  max: number;
  reversed: boolean;
  categories: Array<unknown>;
}

interface INavigatorSeries {
  type: string;
  pointPlacement: number;
  pointPadding: number;
  accessibility: IAccessibility;
}

interface IScrollbar {
  enabled: boolean;
}

interface IRangeSelector {
  enabled: boolean;
  selected?: number;
}

interface IAccessibility {
  enabled: boolean;
}

interface ISeries {
  name: string;
  data: Array<IData>;
  dataLabels: IDataLabels;
}

interface ITitle {
  text: string;
}

interface IDataLabels {
  enabled: boolean;
  formatter: () => string;
  style: IStyle;
  point?: IData;
}

interface ITooltip {
  useHTML: boolean;
  headerFormat: string;
  pointFormatter: () => string;
  start?: number;
  end?: number;
}

interface IStyle {
  color: string;
  textOutline?: string;
}

interface IEvent {
  render: () => void;
}

interface IYAxisLabel {
  useHTML: boolean;
  formatter: () => string;
  value?: string;
}

export interface IData extends Omit<IGanttChartData, "name"> {
  name: string;
}
