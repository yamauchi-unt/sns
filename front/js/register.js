// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    // submitボタン押下イベント
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // submitボタン押下後の処理呼び出し
        registerFormSubmit();
    });
});

/**
 * submitボタン押下後の処理
 */
function registerFormSubmit() {
    // 入力値
    const inputs = {
        user_id: document.getElementById('userId').value.trim(),
        user_name: document.getElementById('userName').value.trim(),
        password: document.getElementById('password1').value.trim(),
        password_confirm: document.getElementById('password2').value.trim(),
    };

    // バリデーションルール
    const rules = {
        user_id: { required: true, max: 30 },
        user_name: { required: true , max: 30},
        password: { required: true},
        password_confirm: { required: true, match: 'password'}
    };

    // エラーメッセージ表示領域
    const errorFields = {
        user_id: 'errorUserId',
        user_name: 'errorUserName',
        password: 'errorPassword1',
        password_confirm: 'errorPassword2'
    };

    // バリデーション実行
    const validator = new Validator(inputs, rules, errorFields);
    // バリデーションエラーがある場合、リターン
    if (!validator.validate()) {
        return;
    }

    // 送信データ
    const postData = {
        user_id: inputs.user_id,
        user_name: inputs.user_name,
        password: inputs.password,
    };

    // リクエスト送信
    fetch(`${API_BASE_URL}users`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(postData)
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 201:
                alert('登録完了');
                window.location.href = 'login.html';
                break;
            case 422:
                return response.json();
            default:
                window.location.href = '500.html';
        }
    })
    // レスポンスボディを処理
    .then(data => {
        if (data.errors) {
            validator.displayErrors(data.errors, errorFields);
        }
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}