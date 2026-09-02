import React from 'react'

const Footer = (props) => (
  <footer className="hidden-xs">
    <div className="container">
      <div className="row">
        <div className="col-xs-12">
          <div className="progress">
            <div
              style={{
                width: `${props.data.totalItemsAnswered * 100 / props.data.totalItems || 0}%`
              }}
              aria-valuemax={props.data.total_questions}
              aria-valuemin="0"
              aria-valuenow={props.data.question_id}
              role="progressbar"
              className="progress-bar progress-bar-info"
            >
                    <span>
                      {props.data.totalItemsAnswered && props.data.totalItems && `${props.data.totalItemsAnswered} / ${props.data.totalItems}`}
                    </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
)

Footer.defaultProps = {
  data: {}
}

export default Footer
