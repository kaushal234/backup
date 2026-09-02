export const selectFactory = (factory) => {
    return{
        type: "SELECT_FACTORY",
        payload: {
            factory
        }
    }
}

export const fillFactories = (factories) => {
    return{
        type: "FETCH_FACTORIES",
        payload: {
            factories
        }
    }
}

export const fillFamilies = (families) => {
    return{
        type: "FILL_FAMILIES",
        payload: {
            families
        }
    }
}

export const fillReport = (body) => {
    const url = `/autoselect.php?m[0]=dashboard&m[1]=displayResearch`
    return{
        type: "FILL_REPORT",
        payload: {
            request: {
                url,
                body
            }
        }
    }
}


export const requestSuccess = (type, payload) => {
    return{
        type: type + "_SUCCEEDED",
        payload
    }
}

export const requestFailed = (type, payload) => {
    return{
        type: type + "_FAILED",
        payload
    }
}
/*
export const fetchFactories = (factories) => {
    return{
        type: "FETCH_FACTORIES",
        payload: {
            request: {
                url:'',
                body:''
            },
            factories
        }
    }
}*/