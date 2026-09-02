const report = {
    axios: {
        baseURL: 'http://localhost/shop',
        responseType: 'json',
    },
    //opt
    options: {
        interceptors: {
            request: [
                (getState, config) => {
                    if (getState().user.token) {
                        config.headers['Authorization'] = 'Bearer ' + getState().user.token
                    }

                    return config
                }
            ],
            response: [
                (getState, response) => {
                    return response
                }
            ]
        }
    }

}

const client = {
    default: report
}

export default client