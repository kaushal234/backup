import React from 'react'

const FourOhFour = () =>
  <main>
    <div className="middle-box text-center animated error400 fadeInDown">
      <h1>404</h1>
      <h3 className="font-bold">{`Page Not Found`}</h3>

      <div className="error-desc">
        {`Sorry, but the page you are looking for has not been found. Try checking the URL for error.`}
      </div>
    </div>
  </main>

export default FourOhFour
