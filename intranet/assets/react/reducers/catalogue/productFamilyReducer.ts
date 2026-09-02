import { AnyAction, Reducer } from "redux";
import { CATALOGUE_FETCH_PRODUCT_FAMILIES, SUCCESS } from "../../constants";

interface IProductFamily {
  productFamilies: Array<any>;
  productFamiliesListIsLoading?: boolean;
}

let initialState: IProductFamily = {
  productFamilies: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.productFamily
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.productFamily,
  };
}

const productFamilyReducer: Reducer<IProductFamily, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const productFamilies: any = {};
  switch (action.type) {
    case CATALOGUE_FETCH_PRODUCT_FAMILIES:
      return {
        ...state,
        productFamiliesListIsLoading: true,
      };
    case CATALOGUE_FETCH_PRODUCT_FAMILIES + SUCCESS:
      action.payload.data["hydra:member"].forEach((product: any) => {
        productFamilies[product["@id"]] = product;
      });
      return {
        ...state,
        productFamilies,
        productFamiliesListIsLoading: false,
      };
    default:
      break;
  }
  return state;
};

export default productFamilyReducer;
