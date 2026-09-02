import React from "react";
import Col from "react-bootstrap/Col";
import Row from "react-bootstrap/Row";

interface IProps {
  title: any;
  role: any;
}

function Group({ title, role }: IProps) {
  return (
    <div className="section py-1">
      <h4 className="fw-bold">{title}</h4>
      <Row>
        <ul className="list-group px-3 d-flex gap-2">
          {role &&
            role.map((roleAccess: any, index: number) => (
              <Col
                key={index}
                className="border border-1 rounded-1 role-access"
              >
                <li className="list-group-item list-group-item-light">
                  {roleAccess.group.name}&nbsp;{" "}
                  <span className="fst-italic">
                    ({roleAccess.group.description})
                  </span>{" "}
                  of {roleAccess.locationProperty}
                </li>
              </Col>
            ))}
          {!role && <p>Aucun rôle disponible</p>}
        </ul>
      </Row>
    </div>
  );
}

export default Group;
