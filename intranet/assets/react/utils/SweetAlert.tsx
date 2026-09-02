import Swal from "sweetalert2";
import React from "react";
import withReactContent from "sweetalert2-react-content";

interface IProps {
  title: any;
  type: any;
  text?: any;
  confirmButtonColor?: any;
  confirmButtonText?: any;
  onConfirm: any;
  onClose?: any;
  show: any;
}

export default class SweetAlert extends React.Component<IProps> {
  sweetAlert: any;

  config: any;

  constructor(props: IProps) {
    super(props);
    const { title, type, text, confirmButtonColor, confirmButtonText } =
      this.props;

    this.sweetAlert = withReactContent(Swal);

    this.config = {
      title,
      icon: type,
      inputValue: text,
      confirmButtonColor,
    };

    if (confirmButtonText) {
      this.config.confirmButtonText = confirmButtonText;
    }
  }

  fire() {
    const { onConfirm, onClose } = this.props;
    this.sweetAlert.fire(this.config).then((result: any) => {
      if (result.isConfirmed) {
        onConfirm();
      }
      if (result.isDismissed) {
        onClose();
      }
    });
  }

  render() {
    const { show } = this.props;
    if (show) {
      this.fire();
    }
    return null;
  }
}
