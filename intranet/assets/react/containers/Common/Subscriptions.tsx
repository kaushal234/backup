import React, { Fragment } from "react";
import { connect } from "react-redux";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import {
  createSubscription as createSubscriptionAction,
  deleteSubscription as deleteSubscriptionAction,
  getSubscriptions as getSubscriptionsAction,
} from "../../actions/common/subscriptionsActions";
import {
  getSubscriptionsMapping,
  isCreationPending,
} from "../../selectors/common/subscriptionsSelector";
import SubscriptionItem from "../../components/Common/SubscriptionItem";
import {
  fetchConnectedUser as fetchConnectedUserAction,
  fetchUsersList,
} from "../../actions/user/userActions";
import { getUserDetails } from "../../selectors/user/userSelectors";
import Loader from "../../components/Loader";
import PeopleAsyncSelect from "../../components/Forms/PeopleAsyncSelect";
import { renderReactInlineSelect } from "../../components/Forms/Elements";
import { AppDispatch, RootState } from "../../store";

type IFormData = any;

interface IProps {
  subscriptions: any;
  getSubscriptions: any;
  resource: any;
  connectedUser: any;
  fetchConnectedUser: any;
  showCreatePeople?: any;
  showDelete?: any;
  showDeleteMyself?: any;
  showSubscribe?: any;
  showSubscribers?: any;
  createSubscription: any;
  creationIsPending: any;
  deleteSubscription: any;
}

type IWrappedProps = IProps & InjectedFormProps<IFormData, IProps>;

class Subscriptions extends React.Component<IWrappedProps> {
  // eslint-disable-next-line react/static-property-placement
  static defaultProps = {
    showCreatePeople: false,
    showDelete: false,
    showDeleteMyself: true,
    showSubscribe: true,
    showSubscribers: true,
  };

  constructor(props: IWrappedProps) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const {
      subscriptions,
      getSubscriptions,
      resource,
      connectedUser,
      fetchConnectedUser,
    } = this.props;
    if (subscriptions === null) {
      getSubscriptions(resource);
      if (!connectedUser["@id"]) {
        fetchConnectedUser();
      }
    }
  }

  render() {
    const {
      resource,
      showCreatePeople,
      showDelete,
      showDeleteMyself,
      showSubscribe,
      showSubscribers,
      subscriptions,
      createSubscription,
      handleSubmit,
      connectedUser,
      creationIsPending,
      deleteSubscription,
    } = this.props;

    if (subscriptions === null) {
      return <p>Loading</p>;
    }

    subscriptions.sort((subscription1: any, subscription2: any) => {
      return subscription2.createdAt - subscription1.createdAt;
    });

    const [currentUserSubscription] = subscriptions.filter(
      (subscription: any) => subscription.user["@id"] === connectedUser["@id"]
    );

    return (
      <div>
        {!!showCreatePeople && (
          <div className="row">
            <form
              className="form-horizontal"
              onSubmit={handleSubmit((values: any) => {
                createSubscription(resource, values.user.value);
              })}
            >
              <h3>{Translator.trans("subscribers.add_subscription")}</h3>
              <div className="row">
                <div className="col-md-6">
                  <PeopleAsyncSelect
                    name="user"
                    required
                    component={renderReactInlineSelect}
                    excluded={subscriptions.map(
                      (subscription: any) => subscription.user["@id"]
                    )}
                  />
                </div>
                <div className="col-md-2 d-flex align-items-end mb-2">
                  <button type="submit" className="btn btn-info btn">
                    {Translator.trans("security.login.submit")}
                  </button>
                </div>
                <div className="col-md-2">
                  {creationIsPending && (
                    <Loader childStyle={{ height: "30px", paddingTop: 10 }} />
                  )}
                </div>
              </div>
            </form>
          </div>
        )}
        {!showCreatePeople && !!showSubscribe && connectedUser && (
          <div className="row">
            <button
              className={`btn btn-${
                currentUserSubscription ? "danger" : "info"
              }`}
              onClick={() => {
                if (currentUserSubscription) {
                  deleteSubscription(resource, currentUserSubscription["@id"]);
                  return;
                }
                createSubscription(resource, connectedUser["@id"]);
              }}
            >
              {Translator.trans(
                currentUserSubscription
                  ? "subscribers.unsubscribe"
                  : "subscribers.subscribe"
              )}
            </button>
          </div>
        )}
        <hr />
        {!!showSubscribers && (
          <div className="row">
            {subscriptions.map((subscription: any, idx: number) => {
              return (
                <Fragment key={subscription["@id"]}>
                  <SubscriptionItem
                    subscription={subscription}
                    showDelete={showDelete}
                    showDeleteMyself={showDeleteMyself}
                    connectedUser={connectedUser}
                  />
                  {(idx + 1) % 4 === 0 && <div className="clearfix" />}
                </Fragment>
              );
            })}
          </div>
        )}
      </div>
    );
  }
}

const formConfiguration = {
  form: "subscription_add",
};

const mapStateToProps = (state: RootState, props: IProps) => {
  const { resource } = props;

  const subscriptions = getSubscriptionsMapping(state, resource);
  const creationIsPending = isCreationPending(state, resource);
  const connectedUser = getUserDetails(state);

  return {
    subscriptions,
    connectedUser,
    creationIsPending,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    fetchConnectedUser: (type: any) => dispatch(fetchConnectedUserAction(type)),
    getSubscriptions: (resource: any) =>
      dispatch(getSubscriptionsAction(resource)),
    getUsers: () => dispatch(fetchUsersList()),
    createSubscription: (resource: any, user: any) =>
      dispatch(
        createSubscriptionAction(resource, user, formConfiguration.form)
      ),
    deleteSubscription: (resourceIri: any, subscriptionIri: any) =>
      dispatch(deleteSubscriptionAction(resourceIri, subscriptionIri)),
  };
};

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(reduxForm<IFormData, IProps>(formConfiguration)(Subscriptions));
