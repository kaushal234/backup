import React from "react";
import Translator from "bazinga-translator";
import "./DocumentTranslationInfo.css";

export default function DocumentTranslationInfo() {
  return (
    <div className="alert alert-info" role="note">
      <strong className="document_translation_info__title">
        {Translator.trans("info_list.title")}
      </strong>
      <ul className="list-unstyled small document_translation_info__list">
        <li>
          <code>docx</code> - {Translator.trans("info_list.docx_description")}
        </li>
        <li>
          <code>pptx</code> - {Translator.trans("info_list.pptx_description")}
        </li>
        <li>
          <code>xlsx</code> - {Translator.trans("info_list.xlsx_description")}
        </li>
        <li>
          <code>pdf</code> - {Translator.trans("info_list.pdf_description")}
        </li>
        <li>
          <code>htm</code>/<code>html</code> -{" "}
          {Translator.trans("info_list.html_description")}
        </li>
        <li>
          <code>txt</code> - {Translator.trans("info_list.txt_description")}
        </li>
        <li>
          <code>xlf</code>/<code>xliff</code> -{" "}
          {Translator.trans("info_list.xlf_description")}
        </li>
        <li>
          <code>srt</code> - {Translator.trans("info_list.srt_description")}
        </li>
      </ul>
    </div>
  );
}
