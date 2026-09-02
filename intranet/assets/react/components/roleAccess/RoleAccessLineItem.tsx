import Translator from "bazinga-translator";
import React from "react";
import RoleSelect from "../Forms/RoleSelect";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  roleAccessLine: any;
}

function RoleAccessLineItem({ roleAccessLine }: IProps) {
  return (
    <>
      <RoleSelect
        required="true"
        label={Translator.trans("mis.specification.user_story_form.role")}
        name={`${roleAccessLine}.group`}
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("mis.specification.user_story_form.location")}
        name={`${roleAccessLine}.locationProperty`}
      />
    </>
  );
}

export default RoleAccessLineItem;
