import { useState } from "react";

interface IProps<T> {
  defaultValue: Array<T>;
}

interface IDynamicField<T> {
  id: number;
  data: T;
  isSubmitDisabled: boolean;
  isFormErrorFree: boolean;
}

export interface IDynamicFieldUpdate<T> {
  data: T;
  isSubmitDisabled: boolean;
  isFormErrorFree: boolean;
}

export const useDynamicField = <T>(props: IProps<T>) => {
  const { defaultValue } = props;

  const defaultState: Array<IDynamicField<T>> = defaultValue.map(
    (item, idx) => ({
      id: idx + 1,
      data: item,
      isSubmitDisabled: false,
      isFormErrorFree: true,
    })
  );

  const [value, setValue] = useState(defaultState);

  const isSubmitDisabled = value.some((field) => field.isSubmitDisabled);

  const isFormErrorFree = value.every((field) => field.isFormErrorFree);

  const [isFormTouched, setIsFormTouched] = useState(false);

  const handleChange = (data: IDynamicField<T>) => {
    setValue((prevState) => {
      const newState = [...prevState];
      const idx = newState.findIndex((field) => field.id === data.id);
      if (idx !== -1) {
        newState[idx].data = data.data;
        newState[idx].isSubmitDisabled = data.isSubmitDisabled;
        newState[idx].isFormErrorFree = data.isFormErrorFree;
      }
      return newState;
    });
  };

  const handleAdd = (data: T) => {
    setValue((prevState) => {
      const newState = [...prevState];
      newState.push({
        id: prevState.length ? prevState[prevState.length - 1].id + 1 : 1,
        data,
        isSubmitDisabled: false,
        isFormErrorFree: true,
      });

      return newState;
    });
  };

  const handleDelete = (id: number) => {
    setValue((prevState) => prevState.filter((field) => field.id !== id));
  };

  const handleTouch = () => {
    setIsFormTouched(true);
  };

  return {
    data: value,
    value: value.map((field) => field.data),
    onChange: handleChange,
    onAdd: handleAdd,
    onDelete: handleDelete,
    touch: handleTouch,
    isFormTouched,
    helper: {
      valueError: isFormErrorFree ? "" : "error",
    },
    fieldProps: {
      error: isSubmitDisabled,
    },
  };
};
