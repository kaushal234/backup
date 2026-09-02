import { client } from "../store/store"


export const factories = (state = [], action) => {
    switch (action.type) {
        case 'FETCH_FACTORIES':
            return action.payload.factories
        case 'FETCH_FACTORIES_FAILURE':
            return []
        case 'FETCH_FACTORIES_SUCCESS':
            return action.payload.response.data

    }
   return state
}

export const families = (state = [], action) => {
    switch (action.type) {
        case 'FILL_FAMILIES':
            return action.payload.families
    }
   return state
}


export const reportSearch = (state = [], action) => {
    switch (action.type) {
        /*case 'FILL_REPORT':
            let response =  client.get(action.request.url,{})
            return {
                fillReport: response
            }*/
        case 'FILL_REPORT_SUCCEEDED':
            return {
                ...state,
                fillReport: action.payload
            }
        case 'FILL_REPORT_FAILED':
            return {

                fillReport: "error"
            }
    }
    return state
}