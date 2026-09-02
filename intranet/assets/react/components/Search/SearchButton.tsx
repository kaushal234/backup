import React from "react";

interface IProps {
  onUserClick: any;
}

function SearchButton(props: IProps) {
  const { onUserClick } = props;

  const handleClick = () => onUserClick();

  return (
    <button
      className="btn btn-danger"
      aria-label="close"
      type="button"
      id="button-addon2"
      onClick={() => handleClick()}
    >
      <i className="fa-sharp fa-solid fa-xmark" />
    </button>
  );
}

export default SearchButton;
