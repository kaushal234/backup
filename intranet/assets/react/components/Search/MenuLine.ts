export default class MenuLine {
  url: any;

  content: any;

  rootParentNodeName: any;

  closestParentNodeName: any;

  agr: any;

  title: any;

  constructor(
    url: any,
    content: any,
    rootParentNodeName: any,
    closestParentNodeName: any,
    agr: any,
    title: any
  ) {
    this.url = url;
    this.content = content;
    this.rootParentNodeName = rootParentNodeName;
    this.closestParentNodeName = closestParentNodeName;
    this.agr = agr;
    this.title = title;
  }

  getSearchString() {
    return `${this.rootParentNodeName} ${this.closestParentNodeName} ${this.content} ${this.agr} ${this.title}`;
  }

  getBreadCrumb() {
    return `${
      this.rootParentNodeName +
      (this.closestParentNodeName !== ""
        ? ` > ${this.closestParentNodeName}`
        : "")
    } > ${this.content}`;
  }
}
