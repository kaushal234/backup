import React, { useEffect, useState } from "react";
import { reduxForm, InjectedFormProps } from "redux-form";
import Translator from "bazinga-translator";
import { LANGUAGES_OPTIONS } from "../../constants/constants";
import { validate } from "../../model/form/extranet_user/validation";
import { IExtranetUserFormData } from "../../types/IExtranetUserFormData";
import {
  IPostExtranetUserWithCrtApiPayload,
  postExtranetUserWithCrt,
} from "../../api/postExtranetUserWithCrt";
import GenericFormComponent from "../../components/GenericFormComponent/GenericFormComponent";
import { IDropdownItem } from "../../types/IDropdownItem";
import { fetchAllCustomerRelationshipTeams } from "../../utils/dropdown/customerRelationshipTeams";
import { toastFailure, toastSuccess } from "../../utils/utils";
import { fetchCountry } from "../../utils/dropdown/country";

interface IProps {
  customers: Array<string>;
}

type IWrappedProps = IProps & InjectedFormProps<IExtranetUserFormData, IProps>;

function ExtranetUserForm(props: IWrappedProps) {
  const { valid, submitting, handleSubmit, customers } = props;

  const [customerRelationshipTeamList, setCustomerRelationshipTeamList] =
    useState<Array<IDropdownItem>>([]);

  const fetchCrts = async () => {
    const response = await fetchAllCustomerRelationshipTeams(
      customers?.[0] ?? ""
    );
    setCustomerRelationshipTeamList(response);
  };
  useEffect(() => {
    fetchCrts();
  }, [customers]);

  const onSubmit = async (values: IExtranetUserFormData) => {
    const extranetUserWithCrtPayload: IPostExtranetUserWithCrtApiPayload = {
      extranetUser: {
        firstname: values?.firstname ?? "",
        lastname: values?.lastname ?? "",
        email: values?.email ?? "",
        username: values?.email ?? "",
        phones: [{ type: "phone", number: values?.phone ?? "" }],
        disabled: false,
        extranetUserProfile: {
          country: values.country?.value ?? "",
          department: values?.department ?? "",
          division: values?.division ?? "",
          jobTitle: values?.jobTitle ?? "",
          language: values?.language?.value ?? "",
        },
      },
      customerRelationshipTeam: values?.crt?.value ?? "",
      groupName: "role_ST",
    };

    const postExtranetUserWithCrtResponse = await postExtranetUserWithCrt(
      extranetUserWithCrtPayload
    );
    if (!postExtranetUserWithCrtResponse.data) {
      await toastFailure(
        postExtranetUserWithCrtResponse.message ??
          Translator.trans("common.error.server")
      );
      return;
    }
    const extranetUser = postExtranetUserWithCrtResponse.data;

    await toastSuccess(
      `${extranetUser.username} ${Translator.trans(
        "directory.extranet_user.success.created"
      )}`
    );
  };

  return (
    <form
      onSubmit={handleSubmit(onSubmit)}
      style={{
        opacity: submitting ? 0.3 : 1,
        pointerEvents: submitting ? "none" : "auto",
      }}
    >
      <div className="row">
        <div className="col-6">
          <GenericFormComponent
            list={customerRelationshipTeamList}
            type="SingleSelectStaticDropdown"
            placeholder={Translator.trans(
              "directory.extranet_user.fields.crt.placeholder"
            )}
            label={Translator.trans("directory.extranet_user.fields.crt.label")}
            name="crt"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.email.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.email.placeholder"
            )}
            name="email"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.lastname.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.lastname.placeholder"
            )}
            name="lastname"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.firstname.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.firstname.placeholder"
            )}
            name="firstname"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.division.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.division.placeholder"
            )}
            name="division"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.department.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.department.placeholder"
            )}
            name="department"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.job_title.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.job_title.placeholder"
            )}
            name="jobTitle"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="Field"
            label={Translator.trans(
              "directory.extranet_user.fields.phone.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.phone.placeholder"
            )}
            name="phone"
            required
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="SingleSelectStaticDropdown"
            label={Translator.trans(
              "directory.extranet_user.fields.language.label"
            )}
            placeholder={Translator.trans(
              "directory.extranet_user.fields.language.placeholder"
            )}
            name="language"
            list={LANGUAGES_OPTIONS}
          />
        </div>
        <div className="col-6">
          <GenericFormComponent
            type="SingleSelectAutoCompleteDropdown"
            label="Country"
            name="country"
            required
            fetchList={fetchCountry}
          />
        </div>
      </div>
      <div className="row">
        <div className="col-12 justify-content-end">
          <div className="col text-end">
            <button
              className={`btn btn-${valid ? "info" : "danger"} m-b-xl`}
              type="submit"
              disabled={submitting || !valid}
            >
              <i className="fa fa-fw fa-save" />
              &nbsp;{Translator.trans("data_table.submit")}
            </button>
          </div>
        </div>
      </div>
    </form>
  );
}

export default reduxForm<IExtranetUserFormData, IProps>({
  form: "extranet_user_form",
  validate,
})(ExtranetUserForm);
