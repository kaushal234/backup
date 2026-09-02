import React, { Fragment, useEffect, useRef, useState } from "react";
import DatePicker from "react-widgets/DatePicker";
import { Field } from "redux-form";
import Select, {
  components,
  GroupBase,
  InputProps,
  OptionProps,
} from "react-select";
import Creatable from "react-select/creatable";
import { CKEditor } from "@ckeditor/ckeditor5-react";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import Form from "react-bootstrap/Form";
import Col from "react-bootstrap/Col";
import Row from "react-bootstrap/Row";
import InputGroup from "react-bootstrap/InputGroup";
import moment from "moment";
import { IconButton, Rating, Tooltip, Typography } from "@mui/material";
import Translator from "bazinga-translator";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import { IVerticalFieldProps } from "../../types/IVerticalFieldProps";
import { IHorizontalFieldProps } from "../../types/IHorizontalFieldProps";
import { IInlineSelectProps } from "../../types/IInlineSelectProps";
import { ISelectProps } from "../../types/ISelectProps";
import { IVerticalSelectProps } from "../../types/IVerticalSelectProps";
import { ITextareaProps } from "../../types/ITextareaProps";
import { IInlineTextareaProps } from "../../types/IInlineTextareaProps";
import { IInlineFileInputProps } from "../../types/IInlineFileInputProps";
import { IInputProps } from "../../types/IInputProps";
import { IVerticalInputProps } from "../../types/IVerticalInputProps";
import { IInlineInputProps } from "../../types/IInlineInputProps";
import { IInlineRadioProps } from "../../types/IInlineRadioProps";
import { ICheckboxProps } from "../../types/ICheckboxProps";
import { IInlineCheckboxProps } from "../../types/IInlineCheckboxProps";
import { IInlineDateTimePickerProps } from "../../types/IInlineDateTimePickerProps";
import { IVerticalDateTimePickerProps } from "../../types/IVerticalDateTimePickerProps";
import { IReactInlineSelectProps } from "../../types/IReactInlineSelectProps";
import { IReactSelectProps } from "../../types/IReactSelectProps";
import { IReactVerticalSelectProps } from "../../types/IReactVerticalSelectProps";
import { IReactRichTextEditorProps } from "../../types/IReactRichTextEditorProps";
import { IReactCreatableProps } from "../../types/IReactCreatableProps";
import { ISwitchProps } from "../../types/ISwitchProps";
import { IRenderInlineRadioProps } from "../../types/IRenderInlineRadioProps";
import { extractDigits, extractFloat } from "../../utils/utils";
import { IVerticalRatingProps } from "../../types/IVerticalRatingProps";
import { IDropdownItem } from "../../types/IDropdownItem";

function VerticalField(props: IVerticalFieldProps) {
  const { children } = props;
  return <Form.Group className="mb-2">{children}</Form.Group>;
}

function HorizontalField(props: IHorizontalFieldProps) {
  const { children } = props;
  return (
    <Form.Group as={Row} className="mb-3">
      {children}
    </Form.Group>
  );
}

export const renderFormError = (touched: any, error: any) => {
  if (touched && error) {
    return (
      <Form.Control.Feedback type="invalid">{error}</Form.Control.Feedback>
    );
  }
  return null;
};

export const renderLabel = (
  label: any,
  inputName: any,
  required: any,
  vertical?: any,
  labelTooltip?: string,
  labelClassName?: string
) => (
  <Form.Label
    htmlFor={inputName}
    column={!vertical}
    sm={2}
    className={`fw-bold col-form-label custom_label ${labelClassName}`}
  >
    <div>
      {required && <span style={{ color: "#ed5565" }}>*&nbsp;</span>}
      {label}
    </div>
    <div>
      {labelTooltip && (
        <Tooltip
          title={
            <span className="tooltip-multiline">
              {Translator.trans(labelTooltip)}
            </span>
          }
          placement="top"
          arrow
        >
          <IconButton>
            <HelpOutlineIcon />
          </IconButton>
        </Tooltip>
      )}
    </div>
  </Form.Label>
);

const renderInlineSelect = ({
  input,
  required,
  meta: { touched, error },
  children,
  ...custom
}: IInlineSelectProps) => (
  <>
    <Form.Select
      {...input}
      {...custom}
      required={required}
      isValid={touched && !error}
      isInvalid={touched && error}
    >
      {children}
    </Form.Select>
    {renderFormError(touched, error)}
  </>
);
export { renderInlineSelect };

const renderSelect = ({
  input,
  label,
  required,
  meta: { touched, error },
  children,
  ...custom
}: ISelectProps) => (
  <HorizontalField>
    {label ? renderLabel(label, input.name, required) : ""}
    <Col sm={10}>
      {renderInlineSelect({
        input,
        label,
        required,
        meta: { touched, error },
        children,
        ...custom,
      })}
    </Col>
  </HorizontalField>
);
export { renderSelect };

const renderVerticalSelect = ({
  input,
  label,
  required,
  meta: { touched, error },
  children,
  ...custom
}: IVerticalSelectProps) => (
  <VerticalField>
    {label ? renderLabel(label, input.name, required, true) : ""}
    {renderInlineSelect({
      input,
      label,
      required,
      meta: { touched, error },
      children,
      ...custom,
    })}
  </VerticalField>
);
export { renderVerticalSelect };

const renderTextarea = ({ input, meta, label, ...custom }: ITextareaProps) => (
  <HorizontalField>
    {renderLabel(label, input.name, custom.required)}
    <Col sm={10}>
      <Form.Control {...input} as="textarea" rows="4" {...custom} />
      {renderFormError(meta.touched, meta.error)}
    </Col>
  </HorizontalField>
);
export { renderTextarea };

const renderInlineTextarea = ({
  input,
  meta,
  parentClassName,
  hideLabel,
  subLabel,
  labelTooltip,
  ...custom
}: IInlineTextareaProps) => (
  <VerticalField>
    <div className={`${parentClassName || ""}`}>
      {!hideLabel && (
        <label
          className={`col-form-label ${
            subLabel && "text_area__label_wrapper"
          } custom_label`}
          htmlFor={input.name}
        >
          <div>
            {custom.required && (
              <span style={{ color: "#ed5565" }}>*&nbsp;</span>
            )}
            {custom.label}
          </div>
          <div>
            {labelTooltip && (
              <Tooltip
                title={Translator.trans(labelTooltip)}
                placement="top"
                arrow
              >
                <IconButton>
                  <HelpOutlineIcon />
                </IconButton>
              </Tooltip>
            )}
          </div>
        </label>
      )}
      {subLabel && (
        <Typography className="text_area__sub_label" variant="caption">
          {subLabel}
        </Typography>
      )}
      <Form.Control
        {...input}
        {...custom}
        rows={`${custom.rows ?? "4"}`}
        as="textarea"
        isValid={meta.touched && !meta.error}
        isInvalid={meta.touched && meta.error}
      />
    </div>
    {renderFormError(meta.touched, meta.error)}
  </VerticalField>
);
export { renderInlineTextarea };

function RenderInlineFileInput({
  input,
  label,
  required,
  meta,
  showError,
  accept,
  labelTooltip,
  multiple,
}: IInlineFileInputProps) {
  const ref = useRef<any>();

  useEffect(() => {
    if (!input.value && ref.current) {
      ref.current.value = null;
    }
  }, [input.value]);

  return (
    <VerticalField>
      {renderLabel(label, input.name, required, true, labelTooltip)}
      <Form.Control
        name={input.name}
        onChange={input.onChange}
        type="file"
        onBlur={showError ? () => input.onBlur(input.value) : undefined}
        accept={accept}
        multiple={multiple}
        ref={ref}
      />
      {renderFormError(meta.touched, meta.error)}
    </VerticalField>
  );
}
export { RenderInlineFileInput as renderInlineFileInput };

const renderInput = ({
  input,
  label,
  type,
  required,
  meta: { touched, error },
  children,
  ...custom
}: IInputProps) => {
  const childrenInput = children || (
    <Form.Control
      {...input}
      {...custom}
      type={type}
      required={required}
      size="lg"
      isInvalid={touched && error}
      isValid={touched && !error}
    />
  );

  return (
    <HorizontalField>
      {renderLabel(label, input.name, required)}
      <Col sm={10}>
        {custom.addon ? (
          <InputGroup className="mb-3" style={{ zIndex: 0 }}>
            {childrenInput}
            <InputGroup.Text>{custom.addon}</InputGroup.Text>
          </InputGroup>
        ) : (
          childrenInput
        )}
        {renderFormError(touched, error)}
      </Col>
    </HorizontalField>
  );
};
export { renderInput };

const renderVerticalInput = ({
  input,
  label,
  type,
  required,
  meta: { touched, error },
  children,
  allowNumbersOnly,
  allowFloatsOnly,
  labelTooltip,
  maxCharacters,
  labelClassName,
  ...custom
}: IVerticalInputProps) => {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let newValue = e.target.value;
    if (allowNumbersOnly) {
      newValue = extractDigits(e.target.value);
    }
    if (allowFloatsOnly) {
      newValue = extractFloat(e.target.value);
    }
    if (maxCharacters) {
      newValue = newValue.slice(0, maxCharacters);
    }
    input.onChange(newValue);
    if (custom.onChange) {
      custom.onChange(e, newValue);
    }
  };

  const childrenInput = children || (
    <Form.Control
      {...input}
      {...custom}
      type={type}
      required={`${required ? "required" : ""}`}
      isInvalid={touched && error}
      isValid={touched && !error}
      onChange={handleChange}
    />
  );

  return (
    <VerticalField>
      {renderLabel(
        label,
        input.name,
        required,
        true,
        labelTooltip,
        labelClassName
      )}
      {custom.addon ? (
        <InputGroup className="mb-3" style={{ zIndex: 0 }}>
          {childrenInput}
          <InputGroup.Text>{custom.addon}</InputGroup.Text>
        </InputGroup>
      ) : (
        childrenInput
      )}
      {renderFormError(touched, error)}
    </VerticalField>
  );
};
export { renderVerticalInput };

const renderInlineInput = ({
  input,
  meta,
  markUserChange,
  customError,
  size,
  labelClassName,
  ...custom
}: IInlineInputProps) => {
  const style: any = {};
  if (
    markUserChange &&
    meta.touched &&
    !meta.autofilled &&
    Number(meta.initial) !== Number(input.value) &&
    !meta.submitting
  ) {
    style.border = "2px solid #f8ac59";
    style.color = "#f8ac59";
    style.fontWeight = "bold";
  }

  const inputChildren = (
    <>
      {custom.label && (
        <Form.Label htmlFor={input.name} className={labelClassName}>
          {custom.label}
        </Form.Label>
      )}
      <Form.Control style={style} {...input} {...custom} size={size || "lg"} />
    </>
  );

  if (custom.addon) {
    return (
      <InputGroup size="sm">
        {inputChildren}
        {custom.addon && <InputGroup.Text>{custom.addon}</InputGroup.Text>}
        {renderFormError(customError, customError)}
      </InputGroup>
    );
  }
  return (
    <>
      {inputChildren}
      {renderFormError(customError, customError)}
    </>
  );
};
export { renderInlineInput };

interface IRadioProps {
  name: any;
  label: any;
  children: any;
}

function Radio(props: IRadioProps) {
  const { name, label, children } = props;
  return (
    <div className="">
      <label className="col-form-label col-sm-2">{label}</label>
      <div className="col-sm-10">
        {children.length > 0 &&
          children.map((child: any, i: any) => (
            <div className="radio radio-primary" key={i}>
              <Field
                component="input"
                name={name}
                id={name + i}
                type="radio"
                value={child.value}
              />
              <label htmlFor={name + i}>{child.label}</label>
            </div>
          ))}
      </div>
    </div>
  );
}
export { Radio };

function InlineRadio(props: IInlineRadioProps) {
  const { name, children, ...custom } = props;
  return (
    <div className="form-check form-check-inline">
      {children.length > 0 &&
        children.map((child: any, i: number) => (
          <div className="radio radio-primary" key={i}>
            <Field
              component="input"
              name={name}
              id={name + i}
              type="radio"
              value={child.value}
              {...custom}
            />
            <label htmlFor={name + i}>{child.label}</label>
          </div>
        ))}
    </div>
  );
}
export { InlineRadio };

const renderCheckbox = ({
  input,
  label,
  required,
  meta: { touched, error },
  disabled = false,
  showError,
  labelTooltip,
  onChange,
}: ICheckboxProps) => (
  <HorizontalField>
    <div className={`${touched && error && "error_wrapper"}`}>
      {renderLabel(label, input.name, required, false, labelTooltip)}
      {showError && renderFormError(touched, error)}
    </div>
    <Col sm={10}>
      <Form.Check
        {...input}
        type="checkbox"
        disabled={disabled}
        checked={input.value}
        isInvalid={touched && error}
        onChange={(value) => {
          input.onChange(value);
          if (onChange) {
            onChange(value);
          }
        }}
      />
    </Col>
  </HorizontalField>
);
export { renderCheckbox };

const renderInlineCheckbox = ({
  input,
  label,
  meta: { touched, error },
  inputStyle,
  ...custom
}: IInlineCheckboxProps) => (
  <Form.Check>
    <Form.Check.Label htmlFor={input.name} {...custom} className="mb-3">
      {label}
    </Form.Check.Label>
    <Form.Check.Input
      isInvalid={touched && error}
      type="checkbox"
      checked={input.value}
      id={input.name}
      {...input}
      style={{ ...inputStyle }}
    />
  </Form.Check>
);
export { renderInlineCheckbox };

const renderInlineDateTimePicker = (props: IInlineDateTimePickerProps) => {
  const {
    input: { onChange, value },
    meta: { touched, error },
    dateformat,
  } = props;

  const { showError, outlineToday = false, ...restProps } = props;

  const today = moment().format("MM/DD/YYYY");
  const css = outlineToday && `div[title="${today}"]{ border: 1px solid grey}`;

  return (
    <>
      <div
        // eslint-disable-next-line react/no-danger
        dangerouslySetInnerHTML={{ __html: `<style>${css}</style>` }}
      />
      <DatePicker
        name={props.input.name}
        {...restProps}
        onChange={(date, rawValue) => {
          onChange(date, rawValue);
          if (props.onChange) {
            props.onChange(date);
          }
        }}
        valueFormat={dateformat || "YYYY-MM-DD"}
        value={!value || !(value instanceof Date) ? null : value}
        calendarProps={{ views: props.views }}
        onBlur={
          showError ? () => props.input.onBlur(props.input.value) : props.onBlur
        }
        onFocus={
          showError
            ? () => props.input.onFocus(props.input.value)
            : props.onFocus
        }
      />
      {renderFormError(touched, error)}
    </>
  );
};
export { renderInlineDateTimePicker };

const renderDateTimePicker = (props: IInlineDateTimePickerProps) => {
  const {
    input: { name },
    label,
    required,
  } = props;
  return (
    <HorizontalField>
      {label ? renderLabel(label, name, required) : ""}
      <Col sm={10}>{renderInlineDateTimePicker(props)}</Col>
    </HorizontalField>
  );
};
export { renderDateTimePicker };

const renderVerticalDateTimePicker = (props: IVerticalDateTimePickerProps) => {
  const {
    input: { name },
    label,
    required,
  } = props;

  const { labelTooltip, ...restProps } = props;

  return (
    <VerticalField>
      {label ? renderLabel(label, name, required, true, labelTooltip) : ""}
      {renderInlineDateTimePicker(restProps)}
    </VerticalField>
  );
};
export { renderVerticalDateTimePicker };

const customStyles = {
  control: (base: any, state: any) => ({
    ...base,
    fontSize: "14px",
    paddingLeft: "6px",
    borderRadius: 0,
    borderColor: state.isFocused ? "#006cb4" : "#939CA3",
    backgroundColor: "#fff",
    color: "inherit",
    boxShadow: "none",
    "&:hover": {},
    ".has-error &": {
      borderColor: "#ed5565",
    },
    minHeight: "36px",
  }),
  dropdownIndicator: (base: any) => ({
    ...base,
    paddingTop: 0,
    paddingBottom: 0,
  }),
  indicatorSeparator: () => ({}),
  menu: (base: any) => ({
    ...base,
    borderRadius: 0,
    marginTop: 0,
  }),
};

const customOption = (
  componentProps: OptionProps<any, false, GroupBase<any>>
) => (
  <components.Option {...componentProps}>
    <div className="custom_option__wrapper">
      <div>{componentProps.children}</div>
      <div className="custom_option__tooltip_wrapper">
        {componentProps.data.tooltip && (
          <Tooltip
            title={Translator.trans(componentProps.data.tooltip)}
            placement="top"
            arrow
          >
            <IconButton>
              <HelpOutlineIcon />
            </IconButton>
          </Tooltip>
        )}
      </div>
    </div>
  </components.Option>
);

const getPlaceholderLength = (placeholder: unknown) => {
  const value = String(placeholder || "");
  return 7.1 * value.length;
};

function customInput(props: InputProps) {
  const { selectProps } = props;
  return (
    <components.Input
      {...props}
      placeholder={String(selectProps.placeholder)}
      style={{
        minWidth: getPlaceholderLength(selectProps.placeholder),
        border: "none",
        outline: "none",
      }}
    />
  );
}

function RenderReactInlineSelect(props: IReactInlineSelectProps) {
  const {
    styles,
    input,
    onChange,
    options,
    isLoadingExternally,
    showError,
    onBlur,
    meta,
    isMulti = false,
    placeholder,
  } = props;

  const [isMenuOpened, setIsMenuOpened] = useState(false);

  const dynamizedStyles = {
    control: (base: any, state: any) => ({
      ...customStyles.control(base, state),
      ...styles,
    }),
    dropdownIndicator: (base: any) => ({
      ...customStyles.dropdownIndicator(base),
    }),
    indicatorSeparator: () => ({
      ...customStyles.indicatorSeparator(),
    }),
    menu: (base: any) => ({
      ...customStyles.menu(base),
    }),
    input: (base: any) => {
      return {
        ...base,
        ...(isMulti && { minWidth: getPlaceholderLength(placeholder) }),
      };
    },
  };

  return (
    <div style={{ width: "100%" }}>
      <Select
        {...props}
        value={input.value}
        styles={dynamizedStyles}
        onChange={(value: any) => {
          input.onChange(value);
          setTimeout(() => {
            if (onChange) {
              onChange(value, value);
            }
          }, 0);
        }}
        options={options}
        isLoading={isLoadingExternally}
        onBlur={showError ? () => input.onBlur() : onBlur}
        className={`select-${input.name}`}
        components={{
          Option: customOption,
          ...(isMulti && { Input: customInput }),
        }}
        controlShouldRenderValue={isMulti || !isMenuOpened}
        onMenuOpen={() => setIsMenuOpened(true)}
        onMenuClose={() => setIsMenuOpened(false)}
      />
      {renderFormError(meta.touched, meta.error)}
    </div>
  );
}
export { RenderReactInlineSelect as renderReactInlineSelect };

const renderReactSelect = (props: IReactSelectProps) => (
  <HorizontalField>
    {renderLabel(props.label, props.input.name, props.required)}
    <Col>{RenderReactInlineSelect(props)}</Col>
  </HorizontalField>
);
export { renderReactSelect };

const renderReactVerticalSelect = (props: IReactVerticalSelectProps) => (
  <VerticalField>
    {renderLabel(
      props.label,
      props.input.name,
      props.required,
      true,
      props.labelTooltip
    )}
    {RenderReactInlineSelect(props)}
  </VerticalField>
);
export { renderReactVerticalSelect };

const renderReactRichTextEditor = (props: IReactRichTextEditorProps) => (
  <VerticalField>
    {renderLabel(
      props.label,
      props.input.name,
      props.required,
      true,
      props.labelTooltip
    )}
    <div className="clearfix z-0" />
    <CKEditor
      data={props.input.value}
      editor={ClassicEditor}
      config={{
        toolbar: {
          items: [
            "undo",
            "redo",
            "|",
            "heading",
            "|",
            "bold",
            "italic",
            "|",
            "bulletedList",
            "numberedList",
            "|",
            "link",
          ],
          shouldNotGroupWhenFull: false,
        },
        plugins: [
          "Bold",
          "Essentials",
          "Heading",
          "Indent",
          "Italic",
          "Link",
          "Paragraph",
          "List",
        ],
        htmlSupport: {
          disallow: [{ name: "img" }, { name: "pre" }],
        },
        placeholder: props.placeholder,
      }}
      onReady={(editor: any) => {
        editor.editing.view.change((writer: any) => {
          writer.setStyle(
            "height",
            props.editorHeight ?? "300px",
            editor.editing.view.document.getRoot()
          );
        });
      }}
      onChange={(event: any, editor: any) =>
        props.input.onChange(editor.getData())
      }
      {...props}
      onBlur={
        props.showError
          ? () => props.input.onBlur(props.input.value)
          : props.onBlur
      }
    />
    {props.showError && renderFormError(props.meta.touched, props.meta.error)}
  </VerticalField>
);
export { renderReactRichTextEditor };

const renderReactCreatable = (props: IReactCreatableProps) => (
  <div
    className={`${props.meta.touched && props.meta.error ? "has-error" : ""}`}
  >
    {renderLabel(props.label, props.input.name, props.required)}
    <div className="col-sm-10">
      <Creatable
        {...props}
        value={props.input.value}
        onChange={(value: any) => props.input.onChange(value)}
        onBlur={() => props.input.onBlur(props.input.value)}
        options={props.options}
        isLoading={props.isloadingExternaly}
      />
      {renderFormError(props.meta.touched, props.meta.error)}
    </div>
    {renderFormError(props.meta.touched, props.meta.error)}
  </div>
);
export { renderReactCreatable };

const renderSwitch = (props: ISwitchProps) => {
  const {
    input,
    label,
    required,
    showError,
    meta: { touched, error },
    labelTooltip,
  } = props;

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    input.onChange(e);
    if (props.onChange) {
      props.onChange(e, e.target.value);
    }
  };

  return (
    <VerticalField>
      <div className="custom-switch-container">
        <div className={`${touched && error && "error_wrapper"}`}>
          {renderLabel(label, input.name, required, true, labelTooltip)}
          {showError && renderFormError(touched, error)}
        </div>
        <Form.Check
          type="switch"
          id={input.name}
          className="custom-switch"
          {...input}
          checked={!!input.value}
          onBlur={
            showError
              ? () => props.input.onBlur(props.input.value)
              : props.onBlur
          }
          onFocus={
            showError
              ? () => props.input.onFocus(props.input.value)
              : props.onFocus
          }
          onChange={handleChange}
        />
      </div>
    </VerticalField>
  );
};

export default renderSwitch;

function renderInlineRadio(props: IRenderInlineRadioProps) {
  const {
    input,
    meta: { touched, error },
    options,
    required,
    label,
    labelTooltip,
  } = props;

  return (
    <div className="custom-radio-container">
      <div>{renderLabel(label, input.name, required, true, labelTooltip)}</div>
      <div className="form-check form-check-inline">
        {options.length > 0 &&
          options.map((option) => (
            <div className="radio radio-primary" key={option.value}>
              <input
                {...input}
                type="radio"
                id={`${input.name}-${option.value}`}
                name={input.name}
                value={option.value}
                checked={input.value === option.value}
              />
              <label htmlFor={`${input.name}-${option.value}`}>
                {option.label}
              </label>
            </div>
          ))}
      </div>
      <div>{renderFormError(touched, error)}</div>
    </div>
  );
}

export { renderInlineRadio };

const renderVerticalRating = (props: IVerticalRatingProps) => {
  const {
    label,
    input,
    required,
    labelTooltip,
    meta: { touched, error },
    list,
    id,
    disabled,
    size,
  } = props;

  const { value } = props.input;
  const matchedIndex = list.findIndex((item) => item.value === value?.value);
  const finalValue = matchedIndex !== -1 ? matchedIndex + 1 : null;

  const handleRatingSelect = (
    event: React.SyntheticEvent<Element, Event>,
    newValue: number | null
  ) => {
    const index = (newValue ?? 1) - 1;
    const defaultItem: IDropdownItem = {
      label: `${newValue ?? 1}`,
      value: `${newValue ?? 1}`,
    };
    props.input.onChange(list?.[index] ?? defaultItem);
  };

  return (
    <VerticalField>
      {renderLabel(label, input.name, required, true, labelTooltip)}
      <div>
        <Rating
          name={input.name}
          value={finalValue}
          size={size ?? "large"}
          disabled={disabled}
          id={id}
          onChange={handleRatingSelect}
        />
      </div>
      {renderFormError(touched, error)}
    </VerticalField>
  );
};

export { renderVerticalRating };
