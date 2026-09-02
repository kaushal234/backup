import React from "react";

interface IProps {
  value: any;
  onChange: any;
  debounce?: number;
  [key: string]: any;
}

export function DebouncedInput({
  value: initialValue,
  onChange,
  debounce = 300,
  ...props
}: IProps) {
  const [value, setValue] = React.useState(initialValue);

  React.useEffect(() => {
    setValue(initialValue);
  }, [initialValue]);

  React.useEffect(() => {
    const timeout = setTimeout(() => {
      onChange(value);
    }, debounce);

    return () => clearTimeout(timeout);
  }, [value, debounce, onChange]);

  return <input {...props} onChange={(e) => setValue(e.target.value)} />;
}
