import React from 'react'

class DisplayReportComponent extends React.Component {

    handleChange(event){
        this.setState({researchReport:event.target.value})
    }

    render() {
        const { researchReport} = this.props
        return (<div> {researchReport} </div>)
    }

}

export default DisplayReportComponent