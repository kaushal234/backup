import React from 'react'
import PropTypes from 'prop-types'
import FiveOhOh from './FiveOhOh'
import FourOhFour from './FourOhFour'
import ThankYou from './ThankYou'

const HandleError = props => {
  switch (props.status) {
    case 423:
      return <ThankYou/>
    case 404:
    default:
      return <FourOhFour/>
    case 500:
      return <FiveOhOh/>
  }
}

HandleError.propTypes = {
  status: PropTypes.number
}

HandleError.defaultProps = {
  status: 0
}

export default HandleError
