import React from "react";
import { connect } from "react-redux";
import { formValueSelector } from "redux-form";
import { getSalesForecastComments } from "../../actions/sfr/sfrActions";
import { getSalesForecastsCommentsMapping } from "../../selectors/sfr/salesForecastSelector";
import Loader from "../Loader";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  salesForecast?: any;
  comments?: any;
  index: any;
  fetchComments?: any;
}

interface IState {
  hideComments: any;
}

class SFRComments extends React.Component<IProps, IState> {
  constructor(props: IProps) {
    super(props);
    this.state = {
      hideComments: false,
    };
  }

  render() {
    const { salesForecast, comments, index, fetchComments } = this.props;

    const { hideComments } = this.state;

    return (
      <div className="sfr-qe-comments">
        <button
          onClick={() => {
            if (comments === null) {
              fetchComments(salesForecast, index);
            }
            this.setState({ hideComments: !hideComments });
          }}
          type="button"
          className={`btn btn-sm ${
            hideComments ? "btn-danger" : "btn-success"
          }`}
        >
          <i className={`fa ${hideComments ? "fa-times" : "fa-comment"}`} />
        </button>
        <div className="sfr-qe-comments-wrapper">
          {hideComments && (
            <div className="ibox">
              <div className="ibox-title">
                <h5>
                  {comments !== null && (
                    <span>
                      {comments.length} comment{comments.length > 1 ? "s" : ""}
                    </span>
                  )}
                </h5>
                <div className="ibox-tools">
                  <a
                    className="close-link"
                    onClick={() => this.setState({ hideComments: false })}
                  >
                    <i className="fa fa-times" />
                  </a>
                </div>
              </div>
              <div className="ibox-content">
                {comments === null && <Loader />}
                <ul>
                  {comments !== null &&
                    comments.map((comment: any, i: number) => {
                      return (
                        <li key={i}>
                          {comment.message}
                          <br />
                          <small>
                            on <i>{comment.createdAt.toDateString()}</i> by{" "}
                            <b>
                              {comment.user.lastname} {comment.user.firstname}
                            </b>
                          </small>
                        </li>
                      );
                    })}
                </ul>
              </div>
            </div>
          )}
        </div>
      </div>
    );
  }
}

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchComments: (salesForecast: any) => {
      dispatch(getSalesForecastComments(salesForecast));
    },
  };
};

const selector = formValueSelector("sfr_quick_edit");

const mapStateToProps = (state: RootState, props: IProps) => {
  const index = !props.index ? 0 : props.index;
  const salesForecast = selector(state, `salesForecasts[${index}]`);
  const comments = getSalesForecastsCommentsMapping(
    state,
    salesForecast["@id"]
  );

  return {
    salesForecast,
    comments,
    index,
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(SFRComments);
