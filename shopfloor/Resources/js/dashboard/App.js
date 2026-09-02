import React, { Component } from 'react';
import {render} from 'react-dom'
import { store } from './store/store'
import { Provider } from 'react-redux'
import SelectFactory from './component/SelectFactory'
import {fillFactories, fillFamilies, fillReport} from "./action/actions";

class App extends Component{

    componentWillMount(){
        let factoriesFromHTML = []
        $('#factories').find('li').each(function(){
            let $this = $(this)
            factoriesFromHTML.push({key:$this.text(), name: $this.text()})
        })

        let familiesFromHTML = []
        $('#families').find('li').each(function(){
            let $this = $(this)
            familiesFromHTML.push({key:$this.attr('data-id'), name:$this.text(), factory: $this.attr('data-factory')})
        })

        store.dispatch(fillFactories(factoriesFromHTML))
        store.dispatch(fillFamilies(familiesFromHTML))
    }

    render () {
        return (
            <div>
            <Provider store ={store}>
                <SelectFactory />
            </Provider>
            </div>
        );
    }
}

export default App;