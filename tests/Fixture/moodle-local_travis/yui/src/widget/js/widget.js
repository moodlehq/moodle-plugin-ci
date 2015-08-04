/**
 * Widget
 *
 * @module moodle-local_travis-widget
 */

/**
 * Does things that widgets are notorious for doing.
 *
 * @constructor
 * @namespace M.local_travis
 * @class Widget
 * @extends Y.Base
 */
function WIDGET() {
    WIDGET.superclass.constructor.apply(this, arguments);
}

WIDGET.NAME = 'moodle-local_travis-widget';

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
            Y.delegate('click', this.handle_click, document, '.local_travis .widget', this);
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

M.local_travis.Widget = WIDGET;
M.local_travis.init_widget = function(config) {
    new WIDGET(config);
};
