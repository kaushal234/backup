import React, { Suspense, useEffect, useState } from "react";
import Translator from "bazinga-translator";
import { Collapse, Divider } from "@mui/material";
import {
  getAllProject,
  IGetAllProjectApiPayload,
} from "../../api/getAllProject";
import { IGanttChartData } from "../../types/IGanttChartData";
import { IProject } from "../../types/IGetAllProjectResponse";
import {
  convertToDateUTCFormat,
  getProjectPhaseColor,
} from "../../utils/utils";
import Accordion from "../../components/Accordion/Accordion";
import GenericFilterForm from "../../components/GenericFilterForm/GenericFilterForm";
import { IGenericFilterFormData } from "../../types/IGenericFilterFormData";
import { PROJECT_IFACTOR_VALUES } from "../../constants/constants";
import { fetchPeople } from "../../utils/dropdown/people";
import { fetchProjectTag } from "../../utils/dropdown/projectTag";
import { fetchBusinessUnit } from "../../utils/dropdown/businessUnit";
import { fetchModule } from "../../utils/dropdown/module";
import "./ProjectChart.css";

const GanttChart = React.lazy(
  () => import("../../components/GanttChart/GanttChart")
);

const convertProjectsToChartData = (projects: Array<IProject>) => {
  const newData: Array<IGanttChartData> = [];
  projects.forEach((project) => {
    project.phases.forEach((phase, idx) => {
      let start: string;
      const end = phase.revisedClosureAt ?? phase.estimatedClosureAt ?? "";
      if (idx === 0) {
        start = project.startedAt;
      } else {
        start =
          project.phases[idx - 1].revisedClosureAt ??
          project.phases[idx - 1].estimatedClosureAt ??
          "";
      }
      newData.push({
        start: convertToDateUTCFormat(start),
        end: convertToDateUTCFormat(end),
        completed: {
          amount: 1,
          fill: getProjectPhaseColor(project.status, idx),
        },
        name: {
          lines: [
            `#${project.id} - ${project.indicesFactor}`,
            project.name,
            `${project.projectManager?.firstname ?? ""} ${
              project.projectManager?.lastname ?? ""
            }`,
            `${project.misOwner?.firstname ?? ""} ${
              project.misOwner?.lastname ?? ""
            }`,
          ],
          link: `/en/private/mis/projects/${project.id}/show`,
        },
        label: `Phase ${phase.number} `,
      });
    });
  });
  return newData;
};

export default function ProjectChart() {
  const [data, setData] = useState<Array<IGanttChartData>>([]);
  const [projectCount, setProjectCount] = useState(0);
  const [isFilterSectionVisible, setIsFilterSectionVisible] = useState(false);

  const fetchData = async (params: IGetAllProjectApiPayload) => {
    const response = await getAllProject(params);
    if (response.status === 200 && response.data) {
      const projects = response.data["hydra:member"];
      const chartData = convertProjectsToChartData(projects);
      setProjectCount(projects.length);
      setData(chartData);
    }
  };

  useEffect(() => {
    fetchData({});
  }, []);

  const handleSubmit = async (values: IGenericFilterFormData) => {
    await fetchData(values);
  };

  return (
    <Accordion
      title={Translator.trans("mis_project.chart.title")}
      onFilterClick={() => setIsFilterSectionVisible((prev) => !prev)}
    >
      <Collapse in={isFilterSectionVisible}>
        <GenericFilterForm
          filterFields={[
            {
              type: "MutliSelectStaticDropdown",
              name: "indicesFactor",
              list: PROJECT_IFACTOR_VALUES,
              label: Translator.trans("mis_project.fields.indices_factor"),
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchPeople,
              name: "projectManager",
              label: Translator.trans("mis_project.fields.project_manager"),
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchPeople,
              name: "misOwner",
              label: Translator.trans("mis_project.fields.mis_owner"),
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchPeople,
              name: "moduleKeyUsers",
              label: Translator.trans("mis_project.fields.module_key_users"),
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchPeople,
              name: "misMembers",
              label: Translator.trans("mis_project.fields.mis_members"),
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchProjectTag,
              name: "tags",
              label: Translator.trans("mis_project.fields.tags"),
              triggerAtChar: 0,
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchBusinessUnit,
              name: "businessUnit",
              label: Translator.trans("mis_project.fields.business_unit"),
              triggerAtChar: 0,
            },
            {
              type: "MutliSelectAutoCompleteDropdown",
              fetchList: fetchModule,
              name: "module",
              label: Translator.trans("mis_project.fields.module"),
              triggerAtChar: 0,
            },
          ]}
          onFormSubmit={handleSubmit}
          submitButtonText={Translator.trans("mis_project.chart.filter")}
        />
        <Divider className="project_chart__divider" />
      </Collapse>
      <Suspense fallback={<div />}>
        <GanttChart data={data} height={300 + projectCount * 90} />
      </Suspense>
    </Accordion>
  );
}
