export function nl2br(str: any, isXhtml: any) {
  const breakTag =
    isXhtml || typeof isXhtml === "undefined" ? "<br />" : "<br>";
  return `${str}`.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, `$1${breakTag}$2`);
}
export function entityToChar(str: any) {
  const textarea = document.createElement("textarea");
  textarea.innerHTML = str;
  return textarea.value;
}

export function isValidDate(date: any) {
  return (
    date &&
    Object.prototype.toString.call(date) === "[object Date]" &&
    !Number.isNaN(date)
  );
}

export function formatDate(date: any) {
  /* Format: yyyy-mm-dd */
  return isValidDate(date)
    ? `${date.getFullYear()}-${`0${date.getMonth() + 1}`.slice(
        -2
      )}-${`0${date.getDate()}`.slice(-2)}`
    : null;
}

export function removeTags(str: any) {
  if (!str) return "";

  str = str.replace(/<\/?(p|ul|li|h1|h2|h3|h4|h5|h6|div|br)>/gi, " ");

  str = str.replace(/(<([^>]+)>)/gi, "");

  str = str.replace(/&nbsp;/gi, " ");

  return str.trim().replace(/\s+/g, " ");
}

export function shortenString(string: any, maxSize = 20) {
  if (!string) return "";

  const strippedString = removeTags(string);

  return strippedString.length > maxSize
    ? `${strippedString.substring(0, maxSize - 1)}...`
    : strippedString;
}
