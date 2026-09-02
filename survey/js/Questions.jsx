import React, { Component } from 'react'
import { client } from './client'
import HandleError from './HandleError'
import Header from './Header'
import Footer from './Footer'
import Stars from './Stars'

class Questions extends Component {
  constructor (props) {
    super(props)

    const currentUrl = props.location.search
    const key = props.match.params.key

    this.handleDescriptionChange = this.handleDescriptionChange.bind(this)
    this.state = {
      data: '',
      loading: true,
      question: '',
      nextQuestion: '',
      ratingTypeValues: {},
      description: '',
      currentUrl: {currentUrl},
      key: {key}
    }

    const url = `/public/surveys/${key}`
    client
      .get(url)
      .then(response => {
        console.log(response.data)
        this.setState({
          data: response.data,
          loading: false
        })
      })
      .catch(error => {
        /* eslint-disable no-console */
        console.log(error.response.status)
        /* eslint-enable no-console */
        this.setState({
          isHTTPError: true,
          status: error.response.status
        })
      })
  }

  getRadioValueAndSendResponse = (value, name) => {
    if (this.state.ratingTypeValues[name] === undefined) {
      client
        .post(`/public/surveys/${this.state.data.token}/answers`, {
          item: this.state.data.campaign.model.items[0]['@id'],
          ratingType: `/surveys/rating_types/${name}`,
          value: value
        })
      this.setState({
        'ratingTypeValues': {...this.state.ratingTypeValues, [name]: value}}
      )
    }
  }

  handleDescriptionChange (event) {
    this.setState({description: event.target.value})
  }

  changeQuestionAndSendComment () {
    this.setState({
      loading: true
    })

    if (this.state.description === 0) {
      return
    }

    client
      .post(`/public/surveys/${this.state.data.token}/comments`, {
        item: this.state.data.campaign.model.items[0]['@id'],
        content: this.state.description
      })
      .then(response => {
        client
          .get(`/public/surveys/${this.state.data.token}`)
          .then(responseGet => {
            this.setState({
              data: responseGet.data,
              loading: false,
              description: '',
              ratingTypeValues: {}
            })
          })
          .catch(error => {
            this.setState({
              isHTTPError: true,
              status: error.response.status
            })
          })
      })
  }

  render () {
    const {isHTTPError, loading, nextQuestion, data, status} = this.state

    if (isHTTPError) {
      return (
        <div>
          <Header/>
          <HandleError status={status}/>
        </div>
      )
    }
    if (loading) {
      return (
        <div>
          <Header name={data && data.campaign.model.items[0].group && data.campaign.model.items[0].group.name}/>
          <div className="spinner text-center">
            <div className="double-bounce1"/>
            <div className="double-bounce2"/>
          </div>
          <Footer data={data}/>
        </div>
      )
    }

    return (
      <div>
        <Header name={data && data.campaign.model.items[0].group && data.campaign.model.items[0].group.name}/>
        <main className="container">
          <div className="row">
            <div className="col-xs-12">
              <h3>
                {data.campaign.model.items[0].description}
              </h3>
              <hr/>
              {data.campaign.model.ratingTypes.map((rt) =>
                <div className="rating-block" key={rt.id}>
                  <h4 className="rating-title">
                    {rt.description}
                  </h4>
                  <Stars
                    savedValue={this.state.ratingTypeValues[rt.id]}
                    min={rt.min}
                    max={rt.max}
                    name={rt.id}
                    minLabel={rt.minLabel}
                    maxLabel={rt.maxLabel}
                    step={1}
                    ratingTypeIndex={rt.id}
                    radioReturn={
                      this.getRadioValueAndSendResponse
                    }
                  />
                  <hr/>
                </div>
              )}
            </div>
          </div>

          <div className="row">
            <div className="col-xs-12">
                <textarea
                  className="form-control"
                  value={data.message}
                  onChange={this.handleDescriptionChange}
                  placeholder="Leave a comment (optional)"
                  style={{resize: 'vertical'}}
                />
            </div>
          </div>

          <div className="row text-right">
            <div className="col-xs-12">
              <button
                onClick={() =>
                  this.changeQuestionAndSendComment(
                    nextQuestion
                  )}
                className="btn btn-lg btn-default"
              >
                Next <span className="glyphicon glyphicon-chevron-right"/>
              </button>
            </div>
          </div>
        </main>
        <Footer data={data}/>
      </div>
    )
  }
}

export default Questions
