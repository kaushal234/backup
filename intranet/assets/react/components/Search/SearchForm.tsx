import React from "react";
import SearchButton from "./SearchButton";

interface IProps {
  onUserInput: any;
  filterText: any;
}

function SearchForm(props: IProps) {
  const { onUserInput, filterText } = props;

  const handleChange = (event: any) => {
    onUserInput(event.target.value);
  };
  const handleClick = () => {
    onUserInput("");
  };

  return (
    <div className="row">
      <div className="input-group mb-3 col-sm-8 col-sm-offset-2">
        <input
          className="form-control"
          placeholder="Search..."
          autoFocus
          value={filterText}
          onChange={(event) => handleChange(event)}
          aria-describedby="button-addon2"
        />
        {filterText !== "" && (
          <SearchButton onUserClick={() => handleClick()} />
        )}
      </div>
    </div>
  );
}

export default SearchForm;
