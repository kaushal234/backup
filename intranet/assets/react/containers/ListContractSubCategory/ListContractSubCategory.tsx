import React, { useEffect, useState } from "react";
import Translator from "bazinga-translator";
import { Accordion } from "react-bootstrap";
import { ISubCategory } from "../../types/IGetAllContractSubCategoryResponse";
import Initializer from "../Initializer/Initializer";
import SimpleTable from "../../components/SimpleTable/SimpleTable";
import "./ListContractSubCategory.css";
import { getAllContractSubCategory } from "../../api/getAllContractSubCategory";

function ListContractSubCategory() {
  const [data, setData] = useState<Array<ISubCategory> | null>(null);

  const fetchData = async () => {
    const response = await getAllContractSubCategory({});
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
      <div className="list_contract_sub_category__wrapper">
        <Accordion
          title={Translator.trans("contract_sub_category.list.accordion.title")}
        >
          <SimpleTable
            headers={[
              {
                value: "contract_sub_category.list.table.name",
                minWidth: "50%",
              },
              {
                value: "contract_sub_category.list.table.category",
                minWidth: "50%",
              },
            ]}
            rows={data.map((item) => [item.name, item.category])}
          />
        </Accordion>
      </div>
    </Initializer>
  );
}

export default ListContractSubCategory;
