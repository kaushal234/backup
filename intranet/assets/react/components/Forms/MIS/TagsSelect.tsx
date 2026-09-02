import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import { getTagsMapping } from "../../../selectors/mis/tagsSelector";
import { RootState } from "../../../store";
import { IDropdownItem } from "../../../types/IDropdownItem";
import GenericFormComponent from "../../GenericFormComponent/GenericFormComponent";

interface IProps {
  tags: Array<IDropdownItem>;
  name: string;
  required?: boolean;
  isDisabled?: boolean;
}

function TagsSelect(props: IProps) {
  const { tags, name = "tags", required, isDisabled } = props;
  return (
    <GenericFormComponent
      type="MutliSelectStaticDropdown"
      list={tags}
      placeholder="Search by name"
      name={name}
      label={Translator.trans("fields.tags")}
      required={required}
      disabled={isDisabled}
    />
  );
}

const mapStateToProps = (state: RootState) => {
  return {
    tags: getTagsMapping(state),
  };
};

export default connect(mapStateToProps)(TagsSelect);
