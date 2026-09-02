import React from 'react'

const Header = (props) => (
  <header className="container-fluid">
    <div className="row">
      <div className="container">
        <div className="row">
          <div className="col-xs-3">
            <img className="img-responsive" src="tld_logo.svg"
                 alt="logo TLD" id="logo"/>
          </div>
          <div className="col-sm-9 text-right">
            <h3>
              {props.name}
            </h3>
          </div>
        </div>
      </div>
    </div>
  </header>
)

Header.defaultProps = {
  name: ''
}

export default Header
