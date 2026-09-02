import _ from "lodash";
import moment from "moment/moment";

const taskFactory = (values: any) => {
  const recipients: Array<any> = [];

  Object.values(values.recipients || []).forEach(({ value }: any) => {
    recipients.push(value);
  });

  return {
    id: values.id ? values.id : null,
    shortDescription: values.shortDescription,
    description: values.description,
    module: _.get(values.module, "value", null),
    referenceId: parseInt(values.referenceId, 10),
    escalationTrigger: parseInt(values.escalationTrigger, 10),
    escalationTriggerUnit: values.escalationTriggerUnit,
    confidential: values.confidential,
    startedAt: moment(values.startedAt).format(),
    dueDate: moment(values.dueDate).format(),
    indiceFactor: values.indiceFactor,
    assignee: _.get(values.assignee, "value", null),
    recipients,
  };
};

export { taskFactory };

const taskFactoryForm = ({
  id,
  shortDescription,
  description,
  module,
  referenceId,
  escalationTrigger,
  escalationTriggerUnit,
  confidential,
  startedAt,
  dueDate,
  indiceFactor,
  assignee,
  recipients,
}: any) => {
  return {
    id,
    shortDescription,
    description,
    module: {
      value: module["@id"],
      label: module.name,
    },
    referenceId,
    escalationTrigger,
    escalationTriggerUnit,
    confidential,
    startedAt: new Date(startedAt),
    dueDate: new Date(dueDate),
    indiceFactor,
    assignees: [
      {
        value: assignee["@id"],
        label: `${assignee.lastname} ${assignee.firstname} - ${assignee.email}`,
      },
    ],
    recipients: recipients.map((recipient: any) => {
      recipient = {
        value: recipient["@id"],
        label: `${recipient.lastname} ${recipient.firstname} - ${recipient.email}`,
      };
      return recipient;
    }),
  };
};

export { taskFactoryForm };
