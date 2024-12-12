xcart.bind(
    'empProcessWPFIframe',
    function (event, params) {
        popup.open(
            '<div id="emp_iframe_wpf_wrapper">'
                + '<iframe id="emp_iframe_wpf" name="emp_iframe_wpf" width="530" height="700" src="'
                + params.url
                + '"></iframe>'
                + '</div>',
            {
                closeOnEscape: false,
                dialogClass: 'no-close'
            }
        );
    }
);
