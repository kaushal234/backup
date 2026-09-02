import React, { useEffect } from "react";
import { change, InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { useParams } from "react-router";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import Accordion from "../Accordion/Accordion";
import "./ContractForm.css";
import { IContractStatusFormData } from "../../types/IContractStatusFormData";
import { CONTRACT_STATUS_OPTIONS } from "../../constants/constants";
import { validate } from "../../model/form/contract_status_form/validation";
import { getContractById } from "../../api/getContractById";
import { useAppDispatch } from "../../hooks/hooks";
import { putContractStatus } from "../../api/putContractStatus";
import { toastFailure } from "../../utils/utils";

const formName = "sample_form";

type IProps = unknown;

type IWrappedProps = IProps &
  InjectedFormProps<IContractStatusFormData, IProps>;

function ContractStatusForm(props: IWrappedProps) {
  const { submitting, handleSubmit, submitFailed, invalid } = props;
  const { id } = useParams();
  const dispatch = useAppDispatch();

  const onSubmit = async (data: IContractStatusFormData) => {
    const response = await putContractStatus({
      id: id ?? "",
      data: {
        status: data.status?.value,
        observationStatus: data.observationStatus,
      },
    });
    if (response.data) {
      window.location.href = `/en/private/legal/contracts/${id}/show`;
    } else {
      toastFailure(response.message);
    }
  };

  const fetchData = async () => {
    const response = await getContractById({ id: id ?? "" });
    if (response.data) {
      const match = CONTRACT_STATUS_OPTIONS.find(
        (item) => item.value === response.data?.status
      );
      dispatch(change(formName, "status", match));
      dispatch(
        change(formName, "observationStatus", response.data.observationStatus)
      );
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <div className="row">
      <div className="col-12 col-md-6 contract-form-column">
        <div className="contract_sub_category_form__wrapper">
          <Accordion title={Translator.trans("contract.form.status.title")}>
            <div>
              <form onSubmit={handleSubmit(onSubmit)}>
                <div className="contract_sub_category_form__section_wrapper">
                  <div className="col-fixed-3">
                    <GenericFormComponent
                      type="SingleSelectStaticDropdown"
                      label={Translator.trans(
                        "contract.form.general.status.label"
                      )}
                      name="status"
                      required
                      list={CONTRACT_STATUS_OPTIONS}
                    />
                  </div>
                  <div className="contract_sub_category_form__row">
                    <GenericFormComponent
                      type="RichTextField"
                      label={Translator.trans(
                        "contract.form.general.status_comment.label"
                      )}
                      name="observationStatus"
                      required
                    />
                  </div>
                </div>

                <div className="contract_sub_category_form__right_align">
                  <button
                    className="btn btn-info mt-3"
                    type="submit"
                    disabled={submitting || (submitFailed && invalid)}
                  >
                    {Translator.trans("contract.form.action.submit")}
                  </button>
                </div>
              </form>
            </div>
          </Accordion>
        </div>
      </div>
    </div>
  );
}

export default reduxForm<IContractStatusFormData, IProps>({
  form: formName,
  enableReinitialize: true,
  validate,
})(ContractStatusForm);
