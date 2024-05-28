
class TokenManager {
    static storageKey = 'token';

    /**
     * トークン保存
     * @return {void}
     */
    static saveToken(rawToken) {
        // プレフィックスを削除
        const cleanToken = rawToken.split('|')[1];
        // セッションストレージに保存
        sessionStorage.setItem(TokenManager.storageKey, cleanToken);
    }

    /**
     * トークン取得
     * @return {string}
     */
    static getToken() {
        let token = sessionStorage.getItem(TokenManager.storageKey);
        return escapeHTML(token);
    }

    /**
     * トークン削除
     * @return {void}
     */
    static removeToken() {
        sessionStorage.removeItem(TokenManager.storageKey);
    }

    /**
     * トークン所持判定
     * @return {void}
     */
    static hasTokenCheck() {
        if (!TokenManager.getToken()) {
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        }
    }
}