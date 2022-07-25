class Chosen {

    static init(selector) {
        $(selector).chosen();
    }

    static update(selector, selected) {

        if (selected) {
            selector.val('')

            for (let val of selected) {

                selector.find(`option[value="${val}"]`).attr('selected', true);
            }
        }

        selector.trigger("chosen:updated");

    }

    static reset(selector) {
        $(selector).val('').trigger("chosen:updated");
    }

}