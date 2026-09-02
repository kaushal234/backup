import React, { Component } from 'react'
import {render } from 'react-dom'
import  App  from "./App.js"


if (document.querySelector('#containerForemanDashboard')) {
    render(
        <App/>,
        document.querySelector('#containerForemanDashboard')
    )
}

