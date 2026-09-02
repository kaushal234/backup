import React from "react";
import { Button, Card, Col, Row } from "react-bootstrap";
import RoleAccessLineItem from "./RoleAccessLineItem";

interface IProps {
  fields: any;
  btnName: any;
}

function RoleAccessLinesArray({ fields, btnName }: IProps) {
  return (
    <>
      <Col className="col-9 col-sm-6 col-md-7 col-lg-3 col-xxl-2 mt-1 mb-1">
        <Button
          name="role-access"
          variant="info"
          className="w-75 d-flex justify-content-start align-items-center ps-4"
          onClick={() =>
            fields.push({
              group: undefined,
              locationProperty: undefined,
            })
          }
        >
          <i className="fa fa-fw fa-plus" />
          &nbsp;{btnName}
        </Button>
      </Col>
      <Row className="d-flex flex-wrap">
        {fields.map((roleAccessLine: any, index: number) => (
          <Col key={index} className="col-md-4">
            <Col className="p-2">
              <Card className="p-3">
                <RoleAccessLineItem roleAccessLine={roleAccessLine} />
                <p className="text-danger fst-italic">
                  help: Location of object | Location type (ex : sso, factory,
                  location)
                </p>
                <Button
                  className=" align-self-start"
                  variant="btn btn-sm btn-danger"
                  type="button"
                  onClick={() => fields.remove(index)}
                >
                  <i className="fa fa-fw fa-trash" />
                </Button>
              </Card>
            </Col>
          </Col>
        ))}
      </Row>
    </>
  );
}
export default RoleAccessLinesArray;
