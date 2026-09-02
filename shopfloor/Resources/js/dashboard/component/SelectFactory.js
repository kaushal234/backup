import React from 'react'
import { connect } from 'react-redux'
import { reduxForm, Field,formValueSelector } from 'redux-form'
import {fillReport} from "../action/actions";

const renderField = ({
                         input,
                         label,
                         type,
                         meta: { touched, error, warning },
                         ...custom
                     }) => (
    <div>
        <label>{label}</label>
        <div>
            <input {...input} placeholder={label} type={type} {...custom} />
            {touched &&
            ((error && <span>{error}</span>) ||
                (warning && <span>{warning}</span>))}
        </div>
    </div>
)


class DashboardForm extends React.Component {

    handleChange(event){
        this.setState({factory:event.target.value})
    }

    onSubmit (values) {
        values.standardHours = values.standardHours || false
        values.families = [values.families]
        const operations = [
            (values.operation1)? "001-004" : '',
            (values.operation2)? "005-499" : '',
            (values.operation3)? "500-599" : '',
            (values.operation4)? "600-899" : '',
            (values.operation5)? "900-970" : '',
            (values.operation6)? "975-995" : '',
        ]
        values.operations = [operations]
        this.props.postForm(values)
    }

    render() {
        const { factories, families,reportValues, standardHours, erFrom} = this.props
        const { handleSubmit, submitting, valid } = this.props
        return (
            <div>
            <div className="container">
            <form className="form-horizontal border p-4" onSubmit={handleSubmit(this.onSubmit.bind(this))}>
                <div className="row">
                    <div className="form-group">
                        <label htmlFor="factory" className="text-center">FACTORIES</label>
                        <Field name="factory" component="select" className="form-control">
                            { factories.map((factory) => <option value={factory.key} key={factory.key}>{factory.name}</option>) }
                        </Field>
                    </div>
                    <div className="form-group col-md-3">
                        <label htmlFor="families" className="text-center">FAMILIES</label>
                        <div>
                            <Field name="families" component="select" multiple={true} type="select-multiple" className="form-control" required={true}>
                                { families && families.length > 0 &&
                                 families.map((family) =><option value={family.name} key={family.key}>{family.name}</option>) }
                            </Field>
                    </div>
                    </div>
                    <div className="form-group col-md-2">
                        <label htmlFor="standardHours" className="text-center control-label">STANDARD HOURS</label>
                        <Field name="standardHours" component="input" type="checkbox" className="form-control"/>
                    </div>
                    <div className="form-group col-md-3">
                       <p className="text-center">
                            ER
                        </p>
                        <div className="row">
                            <label htmlFor="erFrom" className="col-sm-2 control-label">FROM</label>
                            <div className="col-sm-7">
                            <Field name="erFrom"  component="input" className="form-control"/>
                                {erFrom && erFrom.error && <span>{erFrom.error}</span>}
                            </div>
                        </div>
                        <div className="row">
                            <label htmlFor="erTo" className="col-sm-2 control-label" >TO</label>
                            <div className="col-sm-7">
                            <Field name="erTo" component="input" className="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div className="form-group col-md-2">
                        <p>Operations</p>
                        <div className="row">
                            <label htmlFor="operation1" className="control-label">001 to 004</label>
                            <Field name="operation1" component="input" type="checkbox" className="form-control col-md-2" value="001-004"/>
                        </div>
                        <div className="row">
                            <label htmlFor="operation2" className="control-label">005 to 499</label>
                            <Field name="operation2" component="input" type="checkbox" className="form-control col-md-2" value="005-449"/>
                        </div>
                        <div className="row">
                            <label htmlFor="operation3" className="control-label">500 to 599</label>
                            <Field name="operation3" component="input" type="checkbox" className="form-control col-md-2" value="500-599"/>
                        </div>
                        <div className="row">
                            <label htmlFor="operation4" className="control-label">600 to 899</label>
                            <Field name="operation4" component="input" type="checkbox" className="form-control col-md-2" value="600-899"/>
                        </div>
                        <div className="row">
                            <label htmlFor="operation5" className="control-label">900 to 970</label>
                            <Field name="operation5" component="input" type="checkbox" className="form-control col-md-2" value="900-970"/>
                        </div>
                        <div className="row">
                            <label htmlFor="operation6" className="control-label">975 to 995</label>
                            <Field name="operation6" component="input" type="checkbox" className="form-control col-md-2" value="975-995"/>
                        </div>
                    </div>
                </div>
                <div className="form-group col-md-3">
                    <label htmlFor="dateGT" className="control-label">Estimated GT date </label>
                    <Field name="dateGT" component="input" type="date" className="form-control"/>
                </div>
                <div className="row">
                    <div className="form-group">
                        <button type="submit" className="btn btn-primary btn-md" disabled={submitting || !valid }>SUBMIT</button>
                    </div>
                </div>
            </form>
        </div>
                {reportValues &&
                    Object.entries(reportValues).map(
                        ([key,projects]) =>
                            <div className="familyContainer">
                                <table className="descriptionTab  border border-dark">
                                    <tbody>
                                    <tr>
                                        <td colSpan={2} className="text-dark text-center" >
                                            <h4> {key}</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th className="bg-info">OTDP</th>
                                        <th className="bg-info">Last YT/GT promised date</th>
                                    </tr>
                                    <tr>
                                        <th className="bg-info">Operation</th>
                                        <th className="bg-info">Description</th>
                                    </tr>
                                    {Object.entries(projects.operations).map(
                                        ([key,op]) =>
                                            <tr>
                                                <td className="text-center bg-info border border-dark text-white"><b>{op.opno}</b></td>

                                                <td className="operationDescription">
                                                    {op.description}
                                                </td>
                                            </tr>

                                    )}
                                    </tbody>
                                </table>
                                <div className="reportContainer" key={key}>
                                <div className="slideContainer" key={key}>
                                { Object.entries(projects.projects).map(
                                    ([projectNumber,projectsData])=>
                                        <div className="descriptionContainer" >

                                        <table key={projectNumber}>
                                            <tbody>
                                            <tr className="text-dark text-right">
                                                <td colSpan={5} >
                                                    <h4> {projectsData.serialNumber}</h4>
                                                </td>
                                            </tr>
                                            <tr className="text-dark text-right">
                                                <td className="text-center bg-info border border-dark text-white">
                                                    OTDP
                                                </td>
                                                <td colSpan={3} className={(() =>{
                                                        if(new Date(projectsData.estimatedGreenTag) < Date.now()) {
                                                            return "border border-dark text-right bg-danger text-white"
                                                        }

                                                    return "border border-primary text-right"

                                                })()}>
                                                    <b>{projectsData.estimatedGreenTag}</b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th className="bg-info">Operation</th>
                                                {standardHours &&
                                                    <th className="bg-info">Std Hours</th>
                                                }
                                                <th className="bg-info">Percentage</th>
                                            </tr>
                                            {Object.entries(projects.operations).map(
                                                ([key,op]) =>
                                                <tr key={key}>
                                                    <td className="text-center bg-info border border-dark text-white"><b>{op.opno}</b></td>

                                                    {standardHours &&
                                                        < td className="border border-primary text-right">
                                                            {projectsData.operations[op.opno] &&
                                                                <span>{projectsData.operations[op.opno].standardHours || 0}</span>
                                                            }
                                                        </td>
                                                    }
                                                    <td className={(() =>{
                                                        if(projectsData.operations[op.opno]){
                                                            if(projectsData.operations[op.opno].percentage === 100.00) {
                                                                return "border border-dark text-right bg-success text-white"
                                                            }
                                                        }
                                                           return "border border-primary text-right"

                                                    })()}>
                                                        {projectsData.operations[op.opno] &&
                                                        <span>{projectsData.operations[op.opno].percentage}%<span
                                                            className="text-secondary"> ({projectsData.operations[op.opno].nbAnswers}/{projectsData.operations[op.opno].nbQuestions})</span>
                                                        </span>
                                                        }
                                                    </td>
                                                </tr>

                                            )
                                            }
                                            </tbody>
                                        </table>
                                        </div>
                                )
                                }
                            </div>
                        </div>
                            </div>
                    )
                }

            </div>

        )
    }
}

const validate = (values) => {
    const errors = {}

    if (values.erFrom && !/^[t|p][0-9]+$/i.test(values.erFrom)){
        errors.erFrom='Invalid entry'
    }

    if (values.erTo && !values.erFrom && !/^[t|p][0-9]+$/i.test(values.erTo)){
        errors.erTo='Invalid entry'
    }
    return errors
}

const formConfiguration = {
    form: 'select_dashboard',
    validate
}

const mapStateToProps = (state) => {
    const selector = formValueSelector(formConfiguration.form)
    const selectedFactory = selector(state, 'factory')
    const standardHours = selector(state, 'standardHours')
    const families = state.families.filter(({factory}) => factory === selectedFactory)
    return {
        factories: state.factories,
        families,
        reportValues : state.reportSearch.fillReport,
        standardHours,
    }
}

const mapDispachToProps = (dispatch) => {
    return {
        postForm : (values) => dispatch(fillReport(values))
    }
}

export default connect(mapStateToProps,mapDispachToProps)(reduxForm(formConfiguration)(DashboardForm))
