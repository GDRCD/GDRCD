
class TippyWrapper {

    static init(selector, html, options = {}) {
        let {
            placement = 'top',
            trigger = 'mouseenter focus',
            theme = 'gdrcd'
        } = options;

        tippy(selector, {
            content: html,
            allowHTML: true,
            theme,
            interactive: true,
            maxWidth: 500,
            trigger,
            hideOnClick: true,
            placement,
        });
    }

    static initWithAjax(selector, html, options = {}) {
        let {
            placement = 'top',
            trigger = 'mouseenter focus',
            theme = 'gdrcd',
            onShow = () => {},
            onCreate = () => {},
            onHidden = () => {},
        } = options;

        let counter =1;

        tippy(selector, {
            content: html,
            onShow,
            onCreate,
            onHidden,
            allowHTML: true,
            theme,
            interactive: true,
            maxHeight: 50,
            maxWidth: 500,
            trigger,
            hideOnClick: true,
            placement,
        });
    }

}
