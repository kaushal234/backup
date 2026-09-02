import React, { useEffect } from "react";
import { change, InjectedFormProps, reduxForm } from "redux-form";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { ISampleFormData } from "../../types/ISampleFormData";
import { RADIO_OPTIONS, SELECT_OPTIONS } from "../../constants/constants";
import { validate } from "../../model/form/sample_form/validation";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";
import { fetchAirport, fetchAllAirports } from "../../utils/dropdown/airport";
import { toastSuccess } from "../../utils/utils";

const formName = "sample_form";

// eslint-disable-next-line
interface IProps {
  // my component props here
}

type IWrappedProps = IProps & InjectedFormProps<ISampleFormData, IProps>;

function SampleForm(props: IWrappedProps) {
  const dispatch = useAppDispatch();
  const { form, submitting, handleSubmit, submitFailed, invalid } = props;

  const formValues: ISampleFormData | undefined = useAppSelector(
    (state) => state.form[form]?.values
  );
  console.info("render", formValues);

  const onSubmit = async (values: ISampleFormData) => {
    console.info("submit", values);
    await toastSuccess();
  };

  useEffect(() => {
    dispatch(change(formName, "description", "updated value"));
  }, []);

  return (
    <form noValidate onSubmit={handleSubmit(onSubmit)}>
      <GenericFormComponent
        type="Field"
        label="User name"
        placeholder="Enter user name"
        name="username"
        required
        labelTooltip="User name"
      />
      <GenericFormComponent
        type="Field"
        label="Description"
        placeholder="Enter description"
        name="description"
        required
        isTextArea
        labelTooltip="Description"
      />
      <GenericFormComponent
        type="SingleSelectStaticDropdown"
        label="Departure Trip 1"
        name="departure1"
        list={SELECT_OPTIONS}
        required
        labelTooltip="Departure Trip 1"
      />
      <GenericFormComponent
        fetchList={fetchAllAirports}
        type="SingleSelectDynamicDropdown"
        label="Arrival Trip 1"
        name="arrival1"
        required
        labelTooltip="Arrival Trip 1"
      />
      <GenericFormComponent
        type="MutliSelectStaticDropdown"
        label="Departure Trip 2"
        name="departure2"
        list={SELECT_OPTIONS}
        required
        labelTooltip="Departure Trip 2"
      />
      <GenericFormComponent
        fetchList={fetchAllAirports}
        type="MutliSelectDynamicDropdown"
        label="Arrival Trip 2"
        name="arrival2"
        required
        labelTooltip="Arrival Trip 2"
      />
      <GenericFormComponent
        type="SingleSelectAutoCompleteDropdown"
        label="Airport"
        name="airport"
        required
        fetchList={fetchAirport}
        labelTooltip="Airport"
        placeholder="Type to search"
      />
      <GenericFormComponent
        type="MutliSelectAutoCompleteDropdown"
        label="Airports"
        name="airports"
        required
        fetchList={fetchAirport}
        labelTooltip="Airports"
        placeholder="Type to search"
      />
      <GenericFormComponent
        type="RichTextField"
        label="Travel Details"
        placeholder="Enter your travel details"
        name="travelDetails"
        required
        labelTooltip="Travel Details"
      />
      <GenericFormComponent
        type="DatePicker"
        label="Departure Date"
        placeholder="Enter your departure date"
        name="departureDate"
        required
        labelTooltip="Departure Date"
      />
      <GenericFormComponent
        type="Checkbox"
        label="Agree to Terms"
        name="terms"
        required
        labelTooltip="Agree to Terms"
        fitContent
      />
      <GenericFormComponent
        type="Switch"
        label="Agree to Conditions"
        name="conditions"
        required
        labelTooltip="Agree to Conditions"
        fitContent
      />
      <GenericFormComponent
        type="Radio"
        label="Passport Holder"
        name="passportHolder"
        list={RADIO_OPTIONS}
        required
        horizontalOptions
        labelTooltip="Passport Holder"
      />
      <GenericFormComponent
        type="FileInput"
        label="Upload Passport"
        name="passportFile"
        labelTooltip="Upload Passport"
      />
      <button
        className="btn btn-primary mt-3"
        type="submit"
        disabled={submitting || (submitFailed && invalid)}
      >
        Submit
      </button>
    </form>
  );
}

export default reduxForm<ISampleFormData, IProps>({
  form: formName,
  initialValues: {
    username: "Initial Value",
    departure1: SELECT_OPTIONS[0],
    departure2: [SELECT_OPTIONS[0], SELECT_OPTIONS[1]],
    departureDate: new Date(),
    terms: true,
    passportHolder: "no",
  },
  enableReinitialize: true,
  validate,
})(SampleForm);
