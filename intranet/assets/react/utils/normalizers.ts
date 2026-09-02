import _ from "lodash";

export const deburr = (value?: string) => value && _.deburr(value);
