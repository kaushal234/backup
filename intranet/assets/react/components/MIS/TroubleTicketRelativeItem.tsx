import React from "react";
import { connect } from "react-redux";
import Translator from "bazinga-translator";
import {
  createSubscription as createSubscriptionAction,
  getSubscriptions,
} from "../../actions/common/subscriptionsActions";
import { troubleTicketAdditionalOwnerFactory } from "../../model/form/mis/factory";
import { MIS_UPDATE_TROUBLE_TICKET_OWNERS } from "../../constants";
import { getUserDetails } from "../../selectors/user/userSelectors";
import { updateTroubleTicket as updateTroubleTicketAction } from "../../actions/mis/troubleTicketsActions";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  fetchSubscriptions: any;
  troubleTicket: any;
  updateTroubleTicket: any;
  connectedUser: any;
  subscriptions: any;
  createSubscription: any;
}

class TroubleTicketRelativeItem extends React.Component<IProps> {
  componentDidMount() {
    const { fetchSubscriptions, troubleTicket } = this.props;
    fetchSubscriptions(troubleTicket["@id"]);
  }

  updateTroubleTicketAdditionalOwners(troubleTicket: any, user: any) {
    const { updateTroubleTicket } = this.props;
    const troubleTicketObject = troubleTicketAdditionalOwnerFactory(
      troubleTicket,
      user
    );
    updateTroubleTicket(troubleTicketObject);
  }

  userAlreadyOwner(troubleTicket: any, user: any) {
    return (
      user["@id"] === troubleTicket.createdBy["@id"] ||
      Object.values(troubleTicket.additionalOwners || []).filter(
        (owner: any) => user["@id"] === owner["@id"]
      ).length > 0
    );
  }

  userAlreadySubscriber(subscriptions: any, troubleTicket: any, user: any) {
    return (
      user["@id"] === troubleTicket.createdBy["@id"] ||
      Object.values(subscriptions[troubleTicket["@id"]] || []).filter(
        (subscription: any) => user["@id"] === subscription.user["@id"]
      ).length > 0
    );
  }

  render() {
    const { troubleTicket, connectedUser, subscriptions, createSubscription } =
      this.props;

    const assignee =
      troubleTicket.assignee !== null
        ? `${troubleTicket.assignee.lastname} ${troubleTicket.assignee.firstName}`
        : "MIS";

    return (
      <div className="col-md-12">
        <div className="card ">
          <div className="card-header">
            <h3>
              <a
                href={`/en/private/mis/trouble-tickets/${troubleTicket.id}/show`}
              >
                TTS #{troubleTicket.id}
              </a>
            </h3>
          </div>
          <div className="card-body">
            <table className="table table-hover table-responsive association-table">
              <tbody>
                <tr className="back-to-line">
                  <th>{Translator.trans("tasks.assignor")}</th>
                  <td>
                    {troubleTicket.createdBy.lastname}{" "}
                    {troubleTicket.createdBy.firstname}
                  </td>
                </tr>
                <tr className="back-to-line">
                  <th>{Translator.trans("tasks.assignee")}</th>
                  <td>{assignee}</td>
                </tr>
                <tr className="back-to-line">
                  <th>{Translator.trans("fields.status")}</th>
                  <td>{troubleTicket.status}</td>
                </tr>
                <tr className="back-to-line">
                  <th>{Translator.trans("fields.short-description")}</th>
                  <td>{troubleTicket.shortDescription}</td>
                </tr>
              </tbody>
            </table>
            <div className="row justify-content-around">
              <div className="col-md-5 col-sm-12">
                <div className="btn-group" role="group">
                  <button
                    className="btn btn-warning"
                    disabled={this.userAlreadyOwner(
                      troubleTicket,
                      connectedUser
                    )}
                    onClick={() => {
                      this.updateTroubleTicketAdditionalOwners(
                        troubleTicket,
                        connectedUser["@id"]
                      );
                    }}
                  >
                    {Translator.trans("trouble_ticket.fields.same_issue")}
                  </button>
                  <button
                    className="btn"
                    style={{ backgroundColor: "#F3F3F4" }}
                  >
                    {troubleTicket.additionalOwners.length}
                  </button>
                </div>
              </div>
              <div className="col-md-5 col-sm-12">
                <div className="btn-group" role="group">
                  <button
                    className="btn btn-warning"
                    disabled={this.userAlreadySubscriber(
                      subscriptions,
                      troubleTicket,
                      connectedUser
                    )}
                    onClick={() => {
                      createSubscription(
                        troubleTicket["@id"],
                        connectedUser["@id"]
                      );
                    }}
                  >
                    {Translator.trans("trouble_ticket.fields.interested")}
                  </button>
                  <button
                    className="btn"
                    style={{ backgroundColor: "#F3F3F4" }}
                  >
                    {(subscriptions[troubleTicket["@id"]] || []).length}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

const mapStateToProps = (state: RootState) => {
  return {
    subscriptions: state.common.subscriptions,
    connectedUser: getUserDetails(state),
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    createSubscription: (resource: any, user: any) =>
      dispatch(createSubscriptionAction(resource, user)),
    fetchSubscriptions: (resource: any) => dispatch(getSubscriptions(resource)),
    updateTroubleTicket: (troubleTicket: any) =>
      dispatch(
        updateTroubleTicketAction(
          troubleTicket,
          MIS_UPDATE_TROUBLE_TICKET_OWNERS
        )
      ),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(TroubleTicketRelativeItem);
