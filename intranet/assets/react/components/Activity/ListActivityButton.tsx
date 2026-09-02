import React from "react";
import Activity from "../../containers/Activity/Activity";

interface IProps {
  title: any;
  resource: any;
}

interface IState {
  isShown: any;
}

class ListActivityButton extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      isShown: false,
    };
  }

  render() {
    const { title, resource } = this.props;
    const { isShown } = this.state;
    return (
      <div>
        <span
          className="btn btn-primary btn-sm activity-list"
          onClick={() => this.setState({ isShown: !this.state.isShown })}
        >
          <i className="fa fa-book fa-fw" />
        </span>
        {isShown && (
          <div className="activity-list-popup">
            <div className="activity-list-popup-inner">
              {title && <h2>{title}</h2>}
              <a
                className="activity-list-popup-close"
                onClick={() => this.setState({ isShown: false })}
              >
                <i className="fa fa-times fa-fw" />
              </a>
              <Activity resource={resource} />
            </div>
          </div>
        )}
      </div>
    );
  }
}

export default ListActivityButton;
