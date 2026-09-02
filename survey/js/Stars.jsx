import React from 'react'
import PropTypes from 'prop-types'

class Stars extends React.Component {
  constructor (props) {
    super(props)
    // this.defineValue = this.defineValue.bind(this)
    this.state = {
      over: 0
    }
  }

  defineValue (value) {
    this.setState({ over: this.props.savedValue || value })
  }

  render () {
    const listStars = []
    for (
      let i = parseInt(this.props.max, 10);
      i >= parseInt(this.props.min, 10);
      i -= parseInt(this.props.step, 10)
    ) {
      const radioValue = i
      listStars.push(
        <input
          id={`rating-${this.props.ratingTypeIndex}-${radioValue}`}
          key={`rating-${this.props.ratingTypeIndex}-${radioValue}`}
          type="radio"
          name={this.props.ratingTypeIndex}
          value={radioValue}
          onClick={() =>
            this.props.radioReturn(radioValue, this.props.ratingTypeIndex)
          }
          checked={this.props.savedValue === radioValue}
        />
      )
      listStars.push(
        <label
          onMouseEnter={() => this.defineValue(radioValue)}
          onMouseLeave={() => this.defineValue(0)}
          key={`label-${this.props.ratingTypeIndex}-${radioValue}`}
          htmlFor={`rating-${this.props.ratingTypeIndex}-${radioValue}`}
        >{radioValue}</label>
      )
    }

    return (
      <div className="clearfix">
        <div className="row">
          <div className="col-sm-2 hidden-xs rating-label-md">
            <span className="label label-default">
              {this.props.minLabel}
            </span>
          </div>
          <div className="col-sm-8 col-xs-12">
            <span className="star-cb-group">
              {listStars}
              <span className="pull-right star-value">{this.state.over} / {this.props.max}</span>
            </span>
          </div>
          <div className="col-sm-2 hidden-xs text-right rating-label-md">
            <span className="label label-default">
              {this.props.maxLabel}
            </span>
          </div>
        </div>
        <div className="rating-labels visible-xs">
          <span className="pull-left label label-default">
            {this.props.minLabel}
          </span>
          <span className="pull-right label label-default">
            {this.props.maxLabel}
          </span>
        </div>
      </div>
    )
  }
}

Stars.propTypes = {
  min: PropTypes.number,
  max: PropTypes.number,
  step: PropTypes.number,
  ratingTypeIndex: PropTypes.number,
  minLabel: PropTypes.string,
  maxLabel: PropTypes.string,
  radioReturn: PropTypes.func
}

Stars.defaultProps = {
  min: '',
  max: '',
  step: '',
  ratingTypeIndex: '',
  minLabel: '',
  maxLabel: '',
  radioReturn: String
}

export default Stars
