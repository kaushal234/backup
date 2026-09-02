import React from "react";
import SearchResult from "./SearchResult";

interface IProps {
  filterText: any;
  menuLines: any;
}

function SearchResults({ filterText, menuLines }: IProps) {
  const displayMenu = (show = true) => {
    document.querySelectorAll(".accordion-item").forEach((element: any) => {
      element.style.display = show ? "block" : "none";
    });
  };
  if (filterText === "") {
    displayMenu(true);
    return null;
  }
  displayMenu(false);

  const rows = [];

  menuLines.forEach((menuLine: any, i: number) => {
    if (
      !menuLine
        .getSearchString()
        .toLowerCase()
        .includes(filterText.toLowerCase())
    ) {
      return;
    }
    rows.push(
      <SearchResult
        href={menuLine.url}
        name={menuLine.getBreadCrumb()}
        key={i}
        title={menuLine.title}
      />
    );
  });
  if (rows.length === 0) {
    rows.push(<SearchResult href="#" name="No result" key="empty" />);
  }
  return (
    <div id="searchResults" className="quick-search">
      <ul id="search-results" className="nav accordion-collapse active">
        <li>
          <a tabIndex={-1} id="search-result" href="#">
            <i className="fa fa-search fa-fw" />
            <span className="nav-label">Search Results</span>
          </a>
        </li>
        {rows}
      </ul>
    </div>
  );
}

export default SearchResults;
