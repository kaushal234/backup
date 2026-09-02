import React from "react";
import Highcharts from "highcharts";
import HighchartsGantt from "highcharts/modules/gantt";
import HighchartsReact from "highcharts-react-official";
import Translator from "bazinga-translator";
import { IGanttChartData, IName } from "../../types/IGanttChartData";
import { IGanttChartOptions } from "../../types/IGanttChartOptions";
import "./GanttChart.css";

const DEFAULT_GANTT_CHART_OPTIONS: IGanttChartOptions = {
  chart: {
    type: "gantt",
  },
  title: {
    text: "",
  },
  yAxis: {
    uniqueNames: true,
    labels: {
      useHTML: true,
      formatter() {
        const { lines, link }: IName = JSON.parse(this.value ?? "{}");
        const tooltip = lines.join("&#10;");
        return `<div title="${tooltip}" class="gantt_chart__label_wrapper" id=${link}>
          ${lines
            .map((line, idx) => {
              if (idx === 0 && line.includes("-")) {
                const seperated = line.split("-");
                return `<div class="gantt_chart__label">
                  ${seperated[0]} - 
                  <span class="gantt_chart__label_light">${seperated[1]}</span>
                </div>`;
              }
              return `<div class="gantt_chart__label">${line}</div>`;
            })
            .join("")}
        </div>`;
      },
    },
  },
  navigator: {
    enabled: true,
    liveRedraw: true,
    series: {
      type: "gantt",
      pointPlacement: 0.5,
      pointPadding: 0.25,
      accessibility: {
        enabled: false,
      },
    },
    yAxis: {
      min: 0,
      max: 3,
      reversed: true,
      categories: [],
    },
  },
  scrollbar: {
    enabled: true,
  },
  rangeSelector: {
    enabled: true,
  },
  accessibility: {
    enabled: false,
  },
  series: [
    {
      name: "",
      data: [],
      dataLabels: {
        enabled: true,
        formatter() {
          return this.point?.label ?? "";
        },
        style: {
          color: "#000",
          textOutline: "none",
        },
      },
    },
  ],

  tooltip: {
    useHTML: true,
    headerFormat: "",
    pointFormatter() {
      return `${Translator.trans("gantt_chart.start_date")}: ${new Date(
        this.start ?? ""
      ).toDateString()}
        <br />${Translator.trans("gantt_chart.end_date")}: ${new Date(
        this.end ?? ""
      ).toDateString()}
      `;
    },
  },
};

HighchartsGantt(Highcharts);

interface IProps {
  title?: string;
  data: Array<IGanttChartData>;
  height?: number;
}

function GanttChart(props: IProps) {
  const { title, data, height } = props;

  const finalOptions = { ...DEFAULT_GANTT_CHART_OPTIONS };

  finalOptions.title = { text: title ?? "" };

  finalOptions.chart.height = height;

  finalOptions.series[0].data = data.map((item) => ({
    ...item,
    name: JSON.stringify(item.name),
  }));

  finalOptions.chart.events = {
    render: () => {
      document.querySelectorAll(".gantt_chart__label_wrapper").forEach((el) => {
        el.addEventListener("click", () => {
          if (el.id) {
            window.open(el.id, "_blank");
          }
        });
      });
    },
  };

  return (
    <div className="gantt_chart__wrapper">
      <HighchartsReact
        highcharts={Highcharts}
        constructorType="ganttChart"
        options={finalOptions}
      />
    </div>
  );
}

export default GanttChart;
