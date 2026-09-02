import React from 'react'

const FiveOhOh = () =>
  <main>
    <div className="middle-box text-center animated fadeInDown error500">
      <h1>500</h1>
      <h3 className="font-bold">{`Internal Server Error`}</h3>

      <div className="error-desc">
        {`The server encountered something unexpected that didn't allow it to complete the request. We apologize.`}
        <br/>
      </div>
    </div>
  </main>

export default FiveOhOh
