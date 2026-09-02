import _ from "lodash";
import moment from "moment";

const eventFactory = (values: any) => {
  return {
    id: values.id,
    startedAt: moment(values.startedAt).format(),
    endedAt: moment(values.endedAt).format(),
    name: values.name,
    country: _.get(values, "country.value", null),
    state: values.state ?? null,
    dayOff: values.dayOff,
  };
};

const eventFactoryForm = (event: any) => {
  return {
    id: event.id,
    name: event.name,
    state: event.state,
    dayOff: event.dayOff,
    country: event.country
      ? {
          value: event.country["@id"] ?? event.country.id ?? event.country,
          label: event.country.name ?? event.country.code ?? event.country,
        }
      : null,

    startedAt: event.startedAt ? moment(event.startedAt).toDate() : null,
    endedAt: event.endedAt ? moment(event.endedAt).toDate() : null,
  };
};

export { eventFactory, eventFactoryForm };
