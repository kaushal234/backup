import React from "react";
import PropTypes from "prop-types";

interface IProps {
  type: any;
  dismissable: any;
  message: any;
}

function FlashMessage(props: IProps) {
  const { type, dismissable, message } = props;
  return (
    <div
      className={`alert alert-${type} ${
        dismissable ? "alert-dismissable" : ""
      }`}
    >
      {dismissable && (
        <button
          type="button"
          className="close"
          data-dismiss="alert"
          aria-hidden
        >
          &times;
        </button>
      )}
      {message
        .trim()
        .split("\n")
        .map((line: any, key: number) => (
          <span key={key}>
            {line}
            <br />
          </span>
        ))}
    </div>
  );
}

FlashMessage.propTypes = {
  type: PropTypes.string,
  dismissable: PropTypes.bool,
  message: PropTypes.string.isRequired,
};

FlashMessage.defaultProps = {
  type: "danger", // also available 'success' and 'info'
};

export default FlashMessage;
