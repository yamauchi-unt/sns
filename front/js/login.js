// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    // ログインボタン押下イベント
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // ログインボタン押下後の処理呼び出し
        loginFormSubmit();
    });
});

/**
 * ログインボタン押下後の処理
 */
function loginFormSubmit() {
    // 入力値
    const inputs = {
        user_id : document.getElementById('userId').value.trim(),
        password : document.getElementById('password').value.trim(),
    };

    // バリデーションルール
    const rules = {
        user_id: { required: true },
        password: { required: true},
    };

    // エラーメッセージ表示領域
    const errorFields = {
        user_id: 'errorUserId',
        password: 'errorPassword',
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
        password: inputs.password,
    };

    // リクエスト送信
    fetch(`${API_BASE_URL}auth/token`, {
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
            case 200:
                return response.json();
            case 422:
                return response.json().then(data => {
                    throw { status: response.status, data };
                });
            default:
                throw new Error(response.status);
        }
    })
    // レスポンスボディを処理
    .then(data => {
        if (data.token) {
            // トークンをセッションストレージへ保存
            TokenManager.saveToken(data.token);
            window.location.href = 'timeline.html';
        }
    })
    // 例外処理
    .catch(error => {
        if (error.status === 422 && error.data) {
            console.error('Validation error:', error.status);
            console.error('Validation error:', error.data);
            validator.displayErrors(error.data.errors, errorFields);
        } else {
            console.error('Error:', error.message);
            if (error.message.includes('400')) {
                window.location.href = '400.html';
            } else
            if (error.message.includes('401')) {
                const loginError = document.getElementById('errorLogin');
                loginError.textContent = 'ユーザIDまたはパスワードが違います。';
            } else {
                window.location.href = '500.html';
            }
        }
    });
}