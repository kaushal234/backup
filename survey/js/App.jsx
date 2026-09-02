import React from 'react'
import { BrowserRouter, Route, Switch, hashHistory } from 'react-router-dom'
import Questions from './Questions'
import FiveOhOh from './FiveOhOh'
import FourOhFour from './FourOhFour'
import ThankYou from './ThankYou'

const App = () =>
  <BrowserRouter history={hashHistory} basename={process.env.NODE_ENV === 'development' ? '/survey-app' : ''}>
    <Switch basename={process.env.NODE_ENV === 'development' ? 'survey-app' : ''}>
      <Route exact path="/500" component={() => <FiveOhOh/>}/>
      <Route exact path="/423" component={() => <ThankYou/>}/>
      <Route path="/:key" component={Questions}/>
      <Route component={() => <FourOhFour/>}/>
    </Switch>
  </BrowserRouter>

export default App
