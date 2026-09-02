import { AnyAction, Reducer } from "redux";
import {
  CATALOGUE_FETCH_PRODUCT,
  CATALOGUE_FETCH_PRODUCTS,
  CATALOGUE_UPDATE_PRODUCT,
  FAILED,
  PRODUCT_MANUFACTURING_CLEAR_DATA,
  SUCCESS,
} from "../../constants";

interface IProductState {
  products: Array<any>;
  productsListIsLoading?: boolean;
}

let initialState: IProductState = {
  products: [],
};

if (window.SF_INITIAL_STORE_STATE && window.SF_INITIAL_STORE_STATE.product) {
  initialState = { ...initialState, ...window.SF_INITIAL_STORE_STATE.product };
}

const productReducer: Reducer<IProductState, AnyAction> = (
  // eslint-disable-next-line default-param-last
  state = initialState,
  action
) => {
  const products: any = {};
  switch (action.type) {
    case CATALOGUE_UPDATE_PRODUCT:
      return {
        ...state,
        products: {
          ...state.products,
          [action.payload.url]: {
            ...state.products[action.payload.url],
            showLoader: true,
            showSuccess: false,
          },
        },
      };
    case CATALOGUE_UPDATE_PRODUCT + SUCCESS:
      return {
        ...state,
        products: {
          ...state.products,
          [action.payload.data["@id"]]: {
            ...state.products[action.payload.data["@id"]],
            financeFamily: action.payload.data.financeFamily,
            showLoader: false,
            showSuccess: true,
          },
        },
      };
    case CATALOGUE_UPDATE_PRODUCT + FAILED:
      return {
        ...state,
        products: {
          ...state.products,
          [action.payload.url]: {
            ...state.products[action.payload.url],
            financeFamily: action.payload.data.financeFamily,
            showLoader: false,
            showSuccess: false,
          },
        },
      };
    case CATALOGUE_FETCH_PRODUCTS:
      return {
        ...state,
        productsListIsLoading: true,
      };
    case CATALOGUE_FETCH_PRODUCTS + SUCCESS:
      action.payload.data["hydra:member"].forEach((product: any) => {
        products[product["@id"]] = product;
      });
      return {
        ...state,
        products,
        productsListIsLoading: false,
      };
    case CATALOGUE_FETCH_PRODUCT + SUCCESS:
      return {
        ...state,
        products: {
          ...products,
          [action.payload.data["@id"]]: action.payload.data,
        },
      };
    case PRODUCT_MANUFACTURING_CLEAR_DATA:
      [
        "industrialIncorporationParameter",
        "factoryStandardEfficiency",
        "modelBaseHours",
      ].forEach((key) => {
        if (
          Object.hasOwn(
            action.product.productManufacturings[
              `${action.factory}-${action.product["@id"]}-${action.year}`
            ],
            key
          )
        ) {
          action.product.productManufacturings[
            `${action.factory}-${action.product["@id"]}-${action.year}`
          ][key] = null;
        }
      });
      state.products[action.index] = action.product;
      return {
        ...state,
      };
    default:
      break;
  }
  return state;
};

export default productReducer;
