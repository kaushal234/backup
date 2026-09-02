import React from "react";
import "./GlobalChat.css";
import logo from "../../images/alvi_logo.png";

function GlobalChat() {
  return (
    <div className="global_chat__wrapper">
      <a href="/en/private/chat" aria-label="Chatbot">
        <img src={logo} alt="logo" />
      </a>
    </div>
  );
}

export { GlobalChat };
