import React from "react";

interface IProps {
  href: any;
  name: any;
  title?: any;
}

function SearchResult({ href, name, title }: IProps) {
  return (
    <li>
      <a href={href} title={title}>
        {name}
      </a>
    </li>
  );
}

export default SearchResult;
