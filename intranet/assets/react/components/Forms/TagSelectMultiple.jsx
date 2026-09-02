import React from "react";
import { connect } from "react-redux";
import { Field } from "redux-form";
import Translator from "bazinga-translator";
import { renderReactVerticalSelect } from "./Elements";
import { getTagsMapping } from "../../selectors/tag/tagsSelectors";
import { getTags as getTagsAction } from "../../actions/tagsActions";

class TagsSelectMultiple extends React.Component {
  componentDidMount() {
    const { getTags, discriminator, apiRoutePrefix } = this.props;
    getTags(discriminator, apiRoutePrefix);
  }

  render() {
    const { tags, tagsIsLoading, name, ...props } = this.props;
    return (
      <Field
        isMulti
        component="select"
        options={tags}
        isLoading={tagsIsLoading}
        name={name}
        label={Translator.trans("toc.fields.tags.label")}
        placeholder={Translator.trans("toc.fields.tags.placeholder")}
        {...props}
      />
    );
  }
}

TagsSelectMultiple.defaultProps = {
  name: "tags",
  component: renderReactVerticalSelect,
};

const mapStateToProps = (state) => {
  return {
    tags: getTagsMapping(state),
  };
};

const mapDispatchToProps = {
  getTags: getTagsAction,
};

export default connect(mapStateToProps, mapDispatchToProps)(TagsSelectMultiple);
