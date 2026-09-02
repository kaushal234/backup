import React from "react";
import { Field, InjectedFormProps, reduxForm, reset } from "redux-form";
import Swal from "sweetalert2";
import Translator from "bazinga-translator";
import { postTechnicianOnCallPart } from "../../api/postTechnicianOnCallPart";
import { putTechnicianOnCallPart } from "../../api/putTechnicianOnCallPart";
import { setGlobalLoader } from "../../actions/common/loaderActions";
import validate from "../../model/form/technician_on_call/partsValidation";
import {
  renderInlineTextarea,
  renderVerticalInput,
  renderVerticalSelect,
} from "../Forms/Elements";
import { useAppDispatch } from "../../hooks/hooks";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";

type IFormData = any;

interface IProps {
  handleSubmit?: any;
  tocId: any;
  editableTocPartId: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

function TechnicianOnCallPartFormComponent({
  handleSubmit,
  tocId,
  editableTocPartId,
}: IWrappedProps) {
  const dispatch = useAppDispatch();

  const handleFormPart = async ({ tocId: currentTocId, values }: any) => {
    dispatch(setGlobalLoader(true));

    const submittedTocPart = {
      ...values,
      quantity: +values.quantity,
      technicianOnCall: `/service/technician_on_calls/${currentTocId}`,
      replacement: values.replacement || null,
    };

    const writeResponse: any = editableTocPartId
      ? await putTechnicianOnCallPart({
          tocPartId: editableTocPartId,
          values: submittedTocPart,
        })
      : await postTechnicianOnCallPart({ values: submittedTocPart });

    if (writeResponse.data) {
      await dispatch(fetchTechnicianOnCallThunk({ tocId: currentTocId }));
      dispatch(reset("toc_parts"));
      dispatch(setGlobalLoader(false));
      return;
    }

    await Swal.fire({
      icon: "error",
      text: writeResponse.response.data.description,
      title: "Creation failed",
    });

    dispatch(setGlobalLoader(false));
  };

  return (
    <>
      <form
        onSubmit={handleSubmit((values: any) => {
          handleFormPart({ tocId, values });
        })}
      >
        <div className="row justify-content-md-center">
          <div className="col col-8 row">
            <div className="col-3">
              <Field
                name="partNumber"
                label={Translator.trans("toc.fields.parts.part_number")}
                component={renderVerticalInput}
              />
            </div>
            <div className="col-3">
              <Field
                name="vendorPartNumber"
                label={Translator.trans("toc.fields.parts.vendor_part_number")}
                component={renderVerticalInput}
                className="col-4"
              />
            </div>
            <div className="col-3">
              <Field
                name="quantity"
                required
                label={Translator.trans("toc.fields.parts.quantity")}
                component={renderVerticalInput}
                type="number"
              />
            </div>
            <div className="col-3">
              <Field
                name="replacement"
                component={renderVerticalSelect}
                label={Translator.trans("toc.fields.parts.replacement")}
              >
                <option value={undefined} key="null" />
                <option value="SUPPLIER" key="SUPPLIER">
                  {Translator.trans("toc.replacement.supplier")}
                </option>
                <option value="CUSTOMER" key="CUSTOMER">
                  {Translator.trans("toc.replacement.customer")}
                </option>
                <option value="QUOTATION" key="QUOTATION">
                  {Translator.trans("toc.replacement.quotation")}
                </option>
              </Field>
            </div>
            <Field
              name="description"
              required
              label={Translator.trans("toc.fields.parts.description")}
              component={renderInlineTextarea}
            />
            <Field
              name="comment"
              label={Translator.trans("toc.fields.parts.comment")}
              component={renderInlineTextarea}
            />
          </div>
        </div>

        <div className="d-flex justify-content-end">
          <button className="btn btn-info" type="submit">
            {editableTocPartId
              ? Translator.trans("toc.button.parts.edit")
              : Translator.trans("toc.button.parts.create")}
          </button>
        </div>
      </form>

      <hr />
    </>
  );
}

const TechnicianOnCallPartForm = reduxForm<IFormData, IProps>({
  form: "toc_parts",
  validate,
})(TechnicianOnCallPartFormComponent);
export { TechnicianOnCallPartForm };
