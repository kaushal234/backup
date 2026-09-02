import React, { useRef, useState } from "react";
import { Field, WrappedFieldProps } from "redux-form";
import { Form } from "react-bootstrap";
import Translator from "bazinga-translator";
import { renderLabel, renderFormError } from "../Forms/Elements";
import "./GenericFreeTextAutoComplete.css";

export interface IGenericFreeTextAutoCompleteProps<T> {
  name: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
  fetchList: (value: string) => Promise<Array<T>>;
  getOptionLabel: (option: T) => React.ReactNode;
  getOptionValue: (option: T) => string;
  getOptionKey?: (option: T) => string;
  onSelect?: (option: T) => void;
  triggerAtChar?: number;
}

interface IRendererProps<T>
  extends WrappedFieldProps,
    Omit<IGenericFreeTextAutoCompleteProps<T>, "name"> {}

const DEBOUNCE_MS = 400;

function FreeTextInputRenderer<T>({
  input,
  label,
  required,
  labelTooltip,
  meta: { touched, error },
  fetchList,
  getOptionLabel,
  getOptionValue,
  getOptionKey,
  onSelect,
  triggerAtChar = 3,
}: IRendererProps<T>) {
  const [results, setResults] = useState<Array<T>>([]);
  const [isOpen, setIsOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const lastResultsRef = useRef<Array<T>>([]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = e.target;
    input.onChange(value);

    if (debounceRef.current) clearTimeout(debounceRef.current);

    if (value.length < triggerAtChar) {
      setResults([]);
      setIsOpen(false);
      setIsLoading(false);
      return;
    }

    setIsOpen(true);
    setIsLoading(true);

    debounceRef.current = setTimeout(async () => {
      try {
        const list = await fetchList(value);
        lastResultsRef.current = list;
        setResults(list);
      } catch {
        setResults([]);
      } finally {
        setIsLoading(false);
      }
    }, DEBOUNCE_MS);
  };

  const handleChevronMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
    if (isOpen) {
      setIsOpen(false);
      return;
    }
    if (String(input.value).length >= triggerAtChar) {
      setResults(lastResultsRef.current);
      setIsOpen(true);
    }
  };

  const handleSelect = (option: T) => {
    input.onChange(getOptionValue(option));
    setIsOpen(false);
    setResults([]);
    onSelect?.(option);
  };

  const renderDropdownContent = () => {
    if (isLoading) {
      return (
        <li className="list-group-item text-center text-muted">
          <span
            className="spinner-border spinner-border-sm"
            role="status"
            aria-hidden="true"
          />
        </li>
      );
    }
    if (results.length > 0) {
      return results.map((option) => (
        <li
          key={getOptionKey ? getOptionKey(option) : getOptionValue(option)}
          className="list-group-item list-group-item-action"
          role="option"
          aria-selected={false}
          tabIndex={0}
          onMouseDown={() => handleSelect(option)}
          onKeyDown={(e) => {
            if (e.key === "Enter") handleSelect(option);
          }}
        >
          {getOptionLabel(option)}
        </li>
      ));
    }
    return (
      <li className="list-group-item text-muted">
        {Translator.trans("no_results")}
      </li>
    );
  };

  return (
    <Form.Group className="mb-2">
      {renderLabel(label, input.name, required, true, labelTooltip)}
      <div className="generic_free_text_auto_complete__input_wrapper">
        <Form.Control
          {...input}
          id={input.name}
          type="text"
          autoComplete="off"
          isInvalid={touched && !!error}
          isValid={touched && !error}
          onChange={handleChange}
          onBlur={() => setTimeout(() => setIsOpen(false), 150)}
        />
        <i
          className="fa fa-chevron-down generic_free_text_auto_complete__chevron"
          onMouseDown={handleChevronMouseDown}
          onKeyDown={(e) => {
            if (e.key === "Enter" || e.key === " ")
              handleChevronMouseDown(e as unknown as React.MouseEvent);
          }}
          role="button"
          tabIndex={0}
          aria-label="Toggle dropdown"
        />
        {renderFormError(touched, error)}
        {isOpen && (
          <ul
            className="list-group generic_free_text_auto_complete__dropdown"
            role="listbox"
          >
            {renderDropdownContent()}
          </ul>
        )}
      </div>
    </Form.Group>
  );
}

function GenericFreeTextAutoComplete<T>(
  props: IGenericFreeTextAutoCompleteProps<T>
) {
  const { name, ...rest } = props;

  return (
    <Field
      name={name}
      component={
        FreeTextInputRenderer as React.ComponentType<WrappedFieldProps>
      }
      {...rest}
    />
  );
}

export default GenericFreeTextAutoComplete;
