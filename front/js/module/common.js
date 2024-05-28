
/**
 * HTMLエスケープ処理
 * @param {string} str
 * @return {string}
 */
function escapeHTML(str) {
    if (!str) return str;
    return str.replace(/[&<>"']/g, function(match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[match];
    });
}