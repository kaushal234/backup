import sinon from 'sinon'
import React from 'react'
import { shallow, mount } from 'enzyme'
import MockAdapter from 'axios-mock-adapter'
import Questions from '../Questions'
import NumberList from '../NumberList'
import preload from '../../example_get.json'
import { client } from '../client'

describe('In survey ', () => {
  const questionsWrapper = shallow(<Questions/>)

  questionsWrapper.setState({
    data: preload,
    loading: false,
    description: 'aaa'
  })
  // questionsWrapper.setState({ data: preload });

  const numberList = shallow(
    <NumberList
      min={preload.survey.ratingTypes[1].min}
      max={preload.survey.ratingTypes[1].max}
      name={preload.survey.ratingTypes[1].rt_name}
      min_text={preload.survey.ratingTypes[1].minLabel}
      max_text={preload.survey.ratingTypes[1].maxLabel}
      step={1}
      name_of_rt={preload.survey.ratingTypes[1].id}
    />
  )
  // --------------------------------------------
  it(' Questions renders correctly', () => {
    expect(questionsWrapper).toMatchSnapshot()
  })
  // --------------------------------------------
  it('handle click ,get radioValue and good rt Post', () => {
    const mock = new MockAdapter(client)
    mock.onPost('http://jsonplaceholder.typicode.com/todos').reply(200)

    numberList.find('input').first().simulate('click')
    questionsWrapper
      .instance()
      .getRadioValueAndSendResponse(1, preload.survey.ratingTypes[1].id)
    expect(questionsWrapper.state().rt_value).toEqual({
      '2': 1
    })
  })
  // --------------------------------------------
  it('handle click ,get radioValue and bad rt Post', () => {
    const mock = new MockAdapter(client)
    mock.onPost('some/link').reply(200)

    numberList.find('input').first().simulate('click')
    questionsWrapper
      .instance()
      .getRadioValueAndSendResponse(1, preload.survey.ratingTypes[1].id)
    expect(questionsWrapper.state().rt_value).toEqual({
      '2': 1
    })
  })

  // --------------------------------------------

  it('number of radio', () => {
    const listItems = []
    for (
      let i = parseInt(preload.survey.ratingTypes[1].min);
      i <= parseInt(preload.survey.ratingTypes[1].max);
      i += 1
    ) {
      const name_of_rt = 'abc'
      const radioValue = i
      listItems.push(
        <div key={`div-for-input-${radioValue}`} className="radio_wraper">
          <span className="numbers_for_input">
            {radioValue}
          </span>
          <input
            type="radio"
            id={radioValue}
            value={radioValue}
            name={name_of_rt}
            data-postion={radioValue}
            onClick={() =>
              numberList
                .instance()
                .props.radioReturn(
                radioValue,
                preload.survey.ratingTypes[1].name_of_rt
              )}
          />
        </div>
      )
    }
    expect(numberList.find('input').length).toEqual(listItems.length)
    // expect(listItems.length).toEqual(numberList.find("input").length);
  })
  // --------------------------------------------

  it('number of RT', () => {
    expect(preload.survey.ratingTypes.length).toEqual(
      questionsWrapper.find(NumberList).length
    )
  })
  // --------------------------------------------

  it('handleDescriptionChange', () => {
    questionsWrapper
      .find('textarea')
      .simulate('change', {target: {value: 'hello'}})
    questionsWrapper.find('textarea').simulate('bind')
    expect(questionsWrapper.state().description).toEqual('hello')
  })

  // --------------------------------------------
  it('calls componentDidMount and good axios request', () => {
    const callback = sinon.spy(Questions.prototype, 'componentDidMount')
    const mock = new MockAdapter(client)
    mock.onGet('https://api.myjson.com/bins/141ufz').reply(200, preload)
    const wrapper = mount(<Questions/>)
    expect(Questions.prototype.componentDidMount.calledOnce).toEqual(true)
    callback.restore()
  })
  // --------------------------------------------
  it('calls componentDidMount and 500 axios response ', () => {
    const callback = sinon.spy(Questions.prototype, 'componentDidMount')
    const mock = new MockAdapter(client)
    mock.onGet('https://api.myjson.com/bins/141ufz').reply(500, preload)
    const wrapper = mount(<Questions/>)
    expect(Questions.prototype.componentDidMount.calledOnce).toEqual(true)
    callback.restore()
  })
  // --------------------------------------------
  it('calls componentDidMount and 423 axios response ', () => {
    const callback = sinon.spy(Questions.prototype, 'componentDidMount')
    const mock = new MockAdapter(client)
    mock.onGet('https://api.myjson.com/bins/141ufz').reply(423, preload)
    const wrapper = mount(<Questions/>)
    expect(Questions.prototype.componentDidMount.calledOnce).toEqual(true)
    callback.restore()
  })
  // --------------------------------------------
  it('calls componentDidMount and bad axios request', () => {
    const callback = sinon.spy(Questions.prototype, 'componentDidMount')
    const mock = new MockAdapter(client)
    mock.onGet('bad/request/link').reply(200, preload)
    const wrapper = mount(<Questions/>)
    expect(Questions.prototype.componentDidMount.calledOnce).toEqual(true)
    callback.restore()
  })

  // --------------------------------------------
  it('handle Post and get on next button click', () => {
    const mockPost = new MockAdapter(client)
    mockPost.onPost('http://jsonplaceholder.typicode.com/todos').reply(200)

    const questionsWrapper = shallow(<Questions/>)
    questionsWrapper.setState({
      data: preload,
      loading: false,
      description: 'test'
    })
    questionsWrapper.find('.btn-right').simulate('click')
    expect(questionsWrapper.state().description).toEqual('test')
  })
  // --------------------------------------------
  it('handle get on next button click', () => {
    const mockPost = new MockAdapter(client)
    mockPost.onPost('http://jsonplaceholder.typicode.com/todos').reply(200)
    client
      .post(
        'http://jsonplaceholder.typicode.com/todos',
      {
        content: 'sdfsdf',
        item: '/survey/items/2'
      },
      {
        headers: {
          Accept: 'application/ld+json',
          'Content-Type': 'application/ld+json'
        }
      }
      )
      .then((response) => {
        const mockGet = new MockAdapter(client)
        mockGet.onGet('https://api.myjson.com/bins/141ufz').reply(200, preload)
      })

    const questionsWrapper = shallow(<Questions/>)
    questionsWrapper.setState({
      data: preload,
      loading: false,
      description: 'test'
    })
    questionsWrapper.find('.btn-right').simulate('click')
    expect(questionsWrapper.state().description).toEqual('test')
  })
  // --------------------------------------------

  it('handle bad post on next button click', () => {
    const mock = new MockAdapter(client)
    mock.onGet('https://api.myjson.com/bins/141ufz').reply(200, preload)
    questionsWrapper.setState({
      description: 'test'
    })
    questionsWrapper.find('button').first().simulate('click')
    expect(questionsWrapper.state().description).toEqual('test')
  })
})
