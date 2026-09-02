import React, { CSSProperties } from "react";

interface IProps {
  style?: CSSProperties;
  childStyle?: CSSProperties;
}

function Loader({ style, childStyle }: IProps) {
  return (
    <div style={style}>
      <div className="spiner-example" style={childStyle}>
        <div className="sk-spinner sk-spinner-three-bounce">
          <div className="sk-bounce1" />
          <div className="sk-bounce2" />
          <div className="sk-bounce3" />
        </div>
      </div>
    </div>
  );
}

export default Loader;
