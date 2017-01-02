/**
 * String functions for Jaxon
 * See http://javascript.crockford.com/remedial.html for more explanation
 */

/**
 * Substiture variables in the string
 *
 * @return string
 */
if (!String.prototype.supplant) {
    String.prototype.supplant = function (o) {
        return this.replace(
            /{$sRegexp|noescape}/g,
            function (a, b) {
                var r = o[b];
                return typeof r === 'string' || typeof r === 'number' ? r : a;
            }
        );
    };
}
