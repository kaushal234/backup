import React from "react";
import { connect } from "react-redux";
import { setSearchText as setSearchTextAction } from "../../actions/search/searchActions";

import SearchForm from "../../components/Search/SearchForm";
import SearchResults from "../../components/Search/SearchResults";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  search: any;
  setSearchText: any;
  menuLines: any;
}

function Search({ search: { filterText }, setSearchText, menuLines }: IProps) {
  return (
    <div>
      <SearchForm
        filterText={filterText}
        onUserInput={(text: any) => setSearchText(text)}
      />
      <SearchResults filterText={filterText} menuLines={menuLines} />
    </div>
  );
}

const mapStateToProps = ({ search }: RootState) => {
  return {
    search,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    setSearchText: (text: any) => dispatch(setSearchTextAction(text)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(Search);
