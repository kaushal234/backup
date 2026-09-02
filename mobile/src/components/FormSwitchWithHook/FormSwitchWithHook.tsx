import React from "react";
import FormSwitch from "../FormSwitch/FormSwitch";
import { useFormSwitch } from "../../hooks/useFormSwitch";

interface IProps {
  defaultValue?: boolean;
  onChange: (value: boolean) => void;
  dataCy: string;
}

export default function FormSwitchWithHook(props: IProps) {
  const { defaultValue = false, dataCy, onChange } = props;

  const value = useFormSwitch({
    defaultValue,
  });

  return (
    <FormSwitch
      {...value.fieldProps}
      onChange={(e) => {
        value.fieldProps.onChange(e);
        onChange(e.target.checked);
      }}
      dataCy={dataCy}
    />
  );
}
