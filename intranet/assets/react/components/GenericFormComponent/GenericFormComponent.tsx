import React, { ReactNode } from "react";
import "./GenericFormComponent.css";
import GenericSingleSelectAutoCompleteDropdown, {
  IGenericSingleSelectAutoCompleteDropdownProps,
} from "../GenericSingleSelectAutoCompleteDropdown/GenericSingleSelectAutoCompleteDropdown";
import GenericCheckbox, {
  IGenericCheckboxProps,
} from "../GenericCheckbox/GenericCheckbox";
import GenericField, { IGenericFieldProps } from "../GenericField/GenericField";
import GenericMultiSelectStaticDropdown, {
  IGenericMutliSelectStaticDropdownProps,
} from "../GenericMultiSelectStaticDropdown/GenericMultiSelectStaticDropdown";
import GenericSingleSelectStaticDropdown, {
  IGenericSingleSelectStaticDropdownProps,
} from "../GenericSingleSelectStaticDropdown/GenericSingleSelectStaticDropdown";
import GenericSingleSelectDynamicDropdown, {
  IGenericSingleSelectDynamicDropdownProps,
} from "../GenericSingleSelectDynamicDropdown/GenericSingleSelectDynamicDropdown";
import GenericSingleSelectPaginatedDropdown, {
  IGenericSingleSelectPaginatedDropdownProps,
} from "../GenericSingleSelectPaginatedDropdown/GenericSingleSelectPaginatedDropdown";
import GenericMultiSelectDynamicDropdown, {
  IGenericMutliSelectDynamicDropdownProps,
} from "../GenericMultiSelectDynamicDropdown/GenericMultiSelectDynamicDropdown";
import GenericRichTextField, {
  IGenericRichTextFieldProps,
} from "../GenericRichTextField/GenericRichTextField";
import GenericDatePicker, {
  IGenericDatePickerProps,
} from "../GenericDateTimePicker/GenericDateTimePicker";
import GenericSwitch, {
  IGenericSwitchProps,
} from "../GenericSwitch/GenericSwitch";
import GenericRadio, { IGenericRadioProps } from "../GenericRadio/GenericRadio";
import GenericFileInput, {
  IGenericFileInputProps,
} from "../GenericFileInput/GenericFileInput";
import GenericLabel, { IGenericLabelProps } from "../GenericLabel/GenericLabel";
import GenericMultiSelectAutoCompleteDropdown, {
  IGenericMutliSelectAutoCompleteDropdownProps,
} from "../GenericMultiSelectAutoCompleteDropdown/GenericMultiSelectAutoCompleteDropdown";
import GenericRating, {
  IGenericRatingProps,
} from "../GenericRating/GenericRating";
import GenericFormError, {
  IGenericFormErrorProps,
} from "../GenericFormError/GenericFormError";
import GenericFreeTextAutoComplete, {
  IGenericFreeTextAutoCompleteProps,
} from "../GenericFreeTextAutoComplete/GenericFreeTextAutoComplete";

interface ILabel extends IGenericLabelProps {
  type: "Label";
}

interface IError extends IGenericFormErrorProps {
  type: "Error";
  label?: string;
}

interface IField extends IGenericFieldProps {
  type: "Field";
}
interface IRichTextField extends IGenericRichTextFieldProps {
  type: "RichTextField";
}

interface ISingleSelectStaticDropdown
  extends IGenericSingleSelectStaticDropdownProps {
  type: "SingleSelectStaticDropdown";
}

interface ISingleSelectDynamicDropdown
  extends IGenericSingleSelectDynamicDropdownProps {
  type: "SingleSelectDynamicDropdown";
}

interface ISingleSelectPaginatedDropdown
  extends IGenericSingleSelectPaginatedDropdownProps {
  type: "SingleSelectPaginatedDropdown";
}

interface IMutliSelectStaticDropdown
  extends IGenericMutliSelectStaticDropdownProps {
  type: "MutliSelectStaticDropdown";
}

interface IMutliSelectDynamicDropdown
  extends IGenericMutliSelectDynamicDropdownProps {
  type: "MutliSelectDynamicDropdown";
}

interface ISingleSelectAutoCompleteDropdown
  extends IGenericSingleSelectAutoCompleteDropdownProps {
  type: "SingleSelectAutoCompleteDropdown";
}

interface IMutliSelectAutoCompleteDropdown
  extends IGenericMutliSelectAutoCompleteDropdownProps {
  type: "MutliSelectAutoCompleteDropdown";
}

interface ICheckbox extends IGenericCheckboxProps {
  type: "Checkbox";
}

interface ISwitch extends IGenericSwitchProps {
  type: "Switch";
}

interface IDatePicker extends IGenericDatePickerProps {
  type: "DatePicker";
}

interface IRadio extends IGenericRadioProps {
  type: "Radio";
}

interface IFileInput extends IGenericFileInputProps {
  type: "FileInput";
}

interface IRating extends IGenericRatingProps {
  type: "Rating";
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
interface IFreeTextAutoComplete extends IGenericFreeTextAutoCompleteProps<any> {
  type: "FreeTextAutoComplete";
}

type IProps =
  | ILabel
  | IField
  | IRichTextField
  | ISingleSelectStaticDropdown
  | ISingleSelectDynamicDropdown
  | ISingleSelectPaginatedDropdown
  | IMutliSelectStaticDropdown
  | IMutliSelectDynamicDropdown
  | ISingleSelectAutoCompleteDropdown
  | IMutliSelectAutoCompleteDropdown
  | ICheckbox
  | ISwitch
  | IDatePicker
  | IRadio
  | IFileInput
  | IRating
  | IError
  | IFreeTextAutoComplete;

interface ICommonProps {
  hidden?: boolean;
}

function GenericFormComponent(props: IProps & ICommonProps) {
  const { type, label, hidden } = props;

  let component: ReactNode = null;

  if (type === "Label") {
    component = <GenericLabel {...props} />;
  }

  if (type === "Error") {
    const { error } = props;
    component = <GenericFormError {...props} error={error ?? label} />;
  }

  if (type === "Field") {
    component = <GenericField {...props} />;
  }

  if (type === "RichTextField") {
    component = <GenericRichTextField {...props} />;
  }

  if (type === "SingleSelectStaticDropdown") {
    component = <GenericSingleSelectStaticDropdown {...props} />;
  }

  if (type === "SingleSelectDynamicDropdown") {
    component = <GenericSingleSelectDynamicDropdown {...props} />;
  }

  if (type === "SingleSelectPaginatedDropdown") {
    component = <GenericSingleSelectPaginatedDropdown {...props} />;
  }

  if (type === "MutliSelectStaticDropdown") {
    component = <GenericMultiSelectStaticDropdown {...props} />;
  }

  if (type === "MutliSelectDynamicDropdown") {
    component = <GenericMultiSelectDynamicDropdown {...props} />;
  }

  if (type === "SingleSelectAutoCompleteDropdown") {
    component = <GenericSingleSelectAutoCompleteDropdown {...props} />;
  }

  if (type === "MutliSelectAutoCompleteDropdown") {
    component = <GenericMultiSelectAutoCompleteDropdown {...props} />;
  }

  if (type === "Checkbox") {
    component = <GenericCheckbox {...props} />;
  }

  if (type === "Switch") {
    component = <GenericSwitch {...props} />;
  }

  if (type === "DatePicker") {
    component = <GenericDatePicker {...props} />;
  }

  if (type === "Radio") {
    component = <GenericRadio {...props} />;
  }

  if (type === "FileInput") {
    component = <GenericFileInput {...props} />;
  }

  if (type === "Rating") {
    component = <GenericRating {...props} />;
  }

  if (type === "FreeTextAutoComplete") {
    component = <GenericFreeTextAutoComplete {...props} />;
  }

  return (
    <div
      className={`generic_form_component__wrapper 
        ${!label && "generic_form_component__no_label"}
        ${hidden && "generic_form_component__hidden"}`}
    >
      {component}
    </div>
  );
}

export default GenericFormComponent;
