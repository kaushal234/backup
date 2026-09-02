import { JSDOM } from "jsdom";
import jquery from "jquery";

const dom = new JSDOM("<!DOCTYPE html><html><head></head><body></body></html>");

global.window = dom.window;
global.document = dom.window.document;
delete global.navigator;
global.navigator = dom.window.navigator;
global.DOMParser = dom.window.DOMParser;
global.self = global.window;
global.$ = jquery(dom.window);
