import React, { useEffect, useState } from "react";
import Translator from "bazinga-translator";
import { Accordion } from "react-bootstrap";
import { ICategory } from "../../types/IGetAllContractCategoryResponse";
import { getAllContractCategory } from "../../api/getAllContractCategory";
import Initializer from "../Initializer/Initializer";
import SimpleTable from "../../components/SimpleTable/SimpleTable";
import "./ListContractCategory.css";

function ListContractCategory() {
  const [data, setData] = useState<Array<ICategory> | null>(null);

  const fetchData = async () => {
    const response = await getAllContractCategory();
    if (response.status === 200 && response.data) {
      setData(response.data["hydra:member"]);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  if (!data) return null;

  return (
    <Initializer>
      <div className="list_contract_category__wrapper">
        <Accordion
          title={Translator.trans("contract_category.list.accordion.title")}
        >
          <SimpleTable
            headers={[
              { value: "contract_category.list.table.name", minWidth: "100%" },
            ]}
            rows={data.map((item) => [item.name])}
          />
        </Accordion>
      </div>
    </Initializer>
  );
}

export default ListContractCategory;
