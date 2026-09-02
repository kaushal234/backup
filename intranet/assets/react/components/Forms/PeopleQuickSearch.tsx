import React from "react";
import { connect } from "react-redux";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import Loader from "../Loader";
import PeopleAsyncSelect from "./PeopleAsyncSelect";

type IFormData = any;

type IProps = unknown;

interface IState {
  redirecting: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class PeopleQuickSearch extends React.Component<IWrappedProps, IState> {
  constructor(props: IWrappedProps) {
    super(props);
    this.state = {
      redirecting: false,
    };
  }

  redirect({ value: iri }: any) {
    this.setState({ redirecting: true });
    window.location.href = `/en/private/directory${iri}/show`;
  }

  render() {
    const { redirecting } = this.state;
    return !redirecting ? (
      <PeopleAsyncSelect onChange={this.redirect.bind(this)} />
    ) : (
      <div className="text-center">
        <Loader />
        <p>{Translator.trans("being_redirected")}</p>
      </div>
    );
  }
}

export default connect()(
  reduxForm<IFormData, IProps>({ form: "people_quick_search" })(
    PeopleQuickSearch
  )
);
