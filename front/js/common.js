const API_BASE_URL = 'http://localhost/api/';
const ERROR_MSG = {
    user_id: {
        required: 'ユーザIDを入力してください。',
        max: 'ユーザIDは30文字以内で入力してください。',
    },
    user_name: {
        required: 'ユーザ名を入力してください。',
        max: 'ユーザ名は30文字以内で入力してください。',
    },
    password: {
        required: 'パスワードを入力してください。',
    },
    password_confirm: {
        required: 'パスワード（確認用）を入力してください。',
        match: 'パスワード（確認用）を正しく入力してください。',
    },
    current_password: {
        dependent: "現在のパスワードを入力してください。",
    },
    new_password: {
        dependent: "新しいパスワードを入力してください。",
    },
    new_password_confirm: {
        dependent: "新しいパスワード（確認用）を入力してください。",
        match: "新しいパスワード（確認用）を正しく入力してください。"
    },
    image: {
        required: '画像を選択してください。',
    },
    message: {
        required: '本文を入力してください。',
    }
};

class Validator {
    /**
     * @param {Object} inputs
     * @param {Object} rules
     * @param {Object} errorFields
     */
    constructor(inputs, rules, errorFields) {
        this.inputs = inputs;
        this.rules = rules;
        this.errorFields = errorFields;
    }

    /**
     * バリデーション実行
     * @return {boolean}
     */
    validate() {
        let errors = {};

        for (let field in this.rules) {
            let fieldErrors = [];

            // 必須チェック
            if (this.rules[field].required && !this.inputs[field]) {
                fieldErrors.push(ERROR_MSG[field].required);
            }
            // 文字数チェック
            if (this.rules[field].max && this.inputs[field].length > this.rules[field].max) {
                fieldErrors.push(ERROR_MSG[field].max);
            }
            // 確認用パスワードチェック
            if (this.rules[field].match && this.inputs[field] !== this.inputs[this.rules[field].match]) {
                fieldErrors.push(ERROR_MSG[field].match);
            }
            // 依存関係チェック
            if (this.rules[field].dependent && this.inputs[this.rules[field].dependent].length > 0 && !this.inputs[field]) {
                console.log(field);
                fieldErrors.push(ERROR_MSG.dependent);
            }

            // 最初のエラーのみを保存
            if (fieldErrors.length > 0) {
                errors[field] = fieldErrors[0];
            }
        }

        this.displayErrors(errors);
        return Object.keys(errors).length === 0;
    }

    /**
     * エラーメッセージ表示
     * @return {void}
     */
    displayErrors(errors) {
        // エラーメッセージ領域を一旦クリア
        Object.values(this.errorFields).forEach(errorFieldId => {
            const errorElement = document.getElementById(errorFieldId);
            if (errorElement) {
                errorElement.textContent = '';
            }
        });

        // 新しいエラーを設定
        Object.keys(errors).forEach(key => {
            const errorElementId = this.errorFields[key];
            const errorElement = document.getElementById(errorElementId);
            if (errorElement) {
                errorElement.textContent = errors[key];
            }
        });
    }
}

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
        return sessionStorage.getItem(TokenManager.storageKey);
    }

    /**
     * トークン削除
     * @return {void}
     */
    static removeToken() {
        sessionStorage.removeItem(TokenManager.storageKey);
    }
}

class ImageBase64Converter {
    /**
     * エンコード
     * @param {File} file
     * @return {Promise<string>}
     */
    static encode(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const base64DataUrl = event.target.result;
                const base64Data = base64DataUrl.split(',')[1];
                resolve(base64Data);
            };
            reader.onerror = function(error) {
                reject(error);
            };
            reader.readAsDataURL(file);
        });
    }
}

/**
 * HTML要素の値を設定する汎用関数
 * @param {string} elementId - 設定対象のHTML要素のID
 * @param {any} value - 設定する値
 */
function setValueToElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.value = value;
    }
}

