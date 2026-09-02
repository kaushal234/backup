import { CREATE_TASK, EDIT_TASK } from "../../constants";

export function writeTask(taskValues: any, form: any) {
  let url = `/tasks`;
  let type = CREATE_TASK;
  if (taskValues.id) {
    url += `/${taskValues.id}`;
    delete taskValues.id;
    type = EDIT_TASK;
  }
  return {
    type,
    payload: {
      url,
      body: taskValues,
      form,
    },
  };
}
