import React from "react";
import { connect } from "react-redux";
import { deleteSubscription as deleteSubscriptionAction } from "../../actions/common/subscriptionsActions";
import { isDeletionPending } from "../../selectors/common/subscriptionsSelector";
import Loader from "../Loader";
import Subscriber from "./Susbcriber";
import { AppDispatch, RootState } from "../../store";

interface IProps {
  subscription: any;
  showDelete: any;
  showDeleteMyself: any;
  connectedUser: any;
  deleteSubscription?: any;
  deletionIsPending?: any;
}

function SubscriptionItem({
  subscription,
  showDelete,
  showDeleteMyself,
  connectedUser,
  deleteSubscription,
  deletionIsPending,
}: IProps) {
  return (
    <div className="col-md-3" key={subscription["@id"]}>
      <span className="clear">
        <span className="block m-t-xs text-center">
          <Subscriber user={subscription.user} />
        </span>
      </span>
      <span className="text-muted text-xs block text-center">
        {subscription.createdAt.format("MMMM Do YYYY, h:mm:ss a")}
      </span>
      {(!!showDelete ||
        (!!showDeleteMyself &&
          connectedUser["@id"] === subscription.user["@id"])) && (
        <div className="clear">
          <div className="block m-t-xs text-center">
            {deletionIsPending && (
              <Loader childStyle={{ height: "30px", paddingTop: 0 }} />
            )}
            {!deletionIsPending && (
              <button
                className="btn btn-danger btn-sm"
                onClick={() =>
                  deleteSubscription(subscription.resource, subscription["@id"])
                }
              >
                Delete
              </button>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

const mapStateToProps = (state: RootState, props: IProps) => {
  const deletionIsPending = isDeletionPending(state, props.subscription["@id"]);

  return {
    deletionIsPending,
  };
};

const mapDispatchToProps = (dispatch: AppDispatch) => {
  return {
    deleteSubscription: (resourceIri: any, subscriptionIri: any) =>
      dispatch(deleteSubscriptionAction(resourceIri, subscriptionIri)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(SubscriptionItem);
