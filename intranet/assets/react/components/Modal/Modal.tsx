import React, { ReactNode } from "react";
import "./Modal.css";
import { Fade } from "@mui/material";

interface IProps {
  isOpen: boolean;
  title: string;
  children: ReactNode;
  onClose: () => void;
  className?: string;
}

function Modal(props: IProps) {
  const { onClose, isOpen, title, children, className } = props;

  if (!isOpen) return null;

  return (
    <Fade in>
      <div
        className="modal__wrapper modal fade show"
        tabIndex={-1}
        aria-modal="true"
        role="dialog"
      >
        <div className={`modal-dialog modal-lg ${className}`} role="document">
          <div className="modal-content">
            <div className="modal-header">
              <h2>{title}</h2>
              <button
                type="button"
                className="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
                onClick={onClose}
              />
            </div>
            <div className="modal-body">{children}</div>
          </div>
        </div>
      </div>
    </Fade>
  );
}

export default Modal;
