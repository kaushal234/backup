import Translator from "bazinga-translator";
import React from "react";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";

interface IProps {
  peoplePropertyLine: any;
}

function PeoplePropertyLineItem({ peoplePropertyLine }: IProps) {
  return (
    <GenericFormComponent
      type="Field"
      label={Translator.trans(
        "mis.specification.user_story_form.people_property"
      )}
      name={`${peoplePropertyLine}.peopleProperty`}
    />
  );
}

export default PeoplePropertyLineItem;
