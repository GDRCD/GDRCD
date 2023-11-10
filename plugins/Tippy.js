
class TippyWrapper {

    static init(selector, html, theme = 'gdrcd'){
        tippy(selector, {
            content: html,
            allowHTML: true,
            theme: 'gdrcd',
            interactive: true,
            maxWidth: 500
        });
    }

}
