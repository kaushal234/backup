tinymce.init({
    selector: 'textarea:not(.no-editor)',
    browser_spellcheck: true,
    convert_urls: false,
    relative_urls : false,
    height: 250,
    menubar: false,
    plugins: 'lists link autolink paste',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link',

    // disallow all image/media elements
    invalid_elements: 'div,img,picture,figure,figcaption,svg,video,audio,source,iframe,object,embed,pre',


    // --- One-time migration at load: HTML stays as-is; plain text becomes escaped + <br> ---
    setup: function (editor) {
        editor.on('init', function () {
            var el  = editor.getElement(); // the <textarea>
            var raw = (el && typeof el.value === 'string') ? el.value : '';
            if (!raw) return;

            // Normalize all <br> variants: <br>, <br/>, <br />, < br>, < br/>
            raw = raw.replace(/<\s*br\s*\/?\s*>/ig, '<br>');

            // Detect if the initial value already contains allowed HTML
            var hasHtml = /<\/?\s*(?:p|br|a|strong|em|u|s|ul|ol|li|h[1-6]|blockquote|pre|code|span|hr|table|thead|tbody|tfoot|tr|td|th)\b[^>]*>/i
                .test(raw);

            if (hasHtml) {
                // literal sequences "\r\n" "\n" "\r"
                raw = raw.replace(/\\r\\n|\\n|\\r/g, '');
                // HTML path: load as-is (TinyMCE will still remove invalid tags per config)
                editor.setContent(raw);
                return;
            }

            // Plain-text path: escape special chars, then convert newlines to <br>
            raw = raw.replace(/\r\n/g, '\n'); // normalize CRLF

            var withBr = raw.replace(/\n/g, '<br>');
            editor.setContent(withBr, { format: 'html' });
        });
    }
});
