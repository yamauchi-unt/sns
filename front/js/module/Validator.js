
class Validator {

    static ERROR_MSG = {
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
        },
        comment: {
            required: 'コメントを入力してください。',
            max: 'コメントは255文字以内で入力してください。',
        },
    };

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
                fieldErrors.push(Validator.ERROR_MSG[field].required);
            }
            // 文字数チェック
            if (this.rules[field].max && this.inputs[field].length > this.rules[field].max) {
                fieldErrors.push(Validator.ERROR_MSG[field].max);
            }
            // 確認用パスワードチェック
            if (this.rules[field].match && this.inputs[field] !== this.inputs[this.rules[field].match]) {
                fieldErrors.push(Validator.ERROR_MSG[field].match);
            }
            // 依存関係チェック
            if (this.rules[field].dependent) {
                this.rules[field].dependent.forEach(dependency => {
                    if (this.inputs[dependency].length > 0 && !this.inputs[field]) {
                        fieldErrors.push(Validator.ERROR_MSG[field].dependent);
                    }
                });
            }

            // 最初のエラーのみを保存
            if (fieldErrors.length > 0) {
                errors[field] = fieldErrors[0];
            }
        }

        // エラーメッセージ表示
        this.displayErrors(errors);
        // エラーが無ければtrueを返す
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