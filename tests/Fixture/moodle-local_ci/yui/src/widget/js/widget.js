/**
 * Widget
 *
 * @module moodle-local_ci-widget
 */

/**
 * Does things that widgets are notorious for doing.
 *
 * @constructor
 * @namespace M.local_ci
 * @class Widget
 * @extends Y.Base
 */
function WIDGET() {
    WIDGET.superclass.constructor.apply(this, arguments);
}

WIDGET.NAME = 'moodle-local_ci-widget';

WIDGET.ATTRS = {
    /**
     * Current context ID
     *
     * @attribute contextId
     * @type Number
     * @default undefined
     * @required
     */
    contextId: {value: undefined}
};

Y.extend(WIDGET, Y.Base,
    {
        /**
         * Setup the app
         */
        initializer: function() {
            Y.delegate('click', this.handle_click, document, '.local_ci .widget', this);
        },

        /**
         * Handles the click
         * @param e
         * @method bind
         */
        handle_click: function(e) {
            // No click for you!
            e.preventDefault();
        }
    }
);

M.local_ci.Widget = WIDGET;
M.local_ci.init_widget = function(config) {
    new WIDGET(config);
};
