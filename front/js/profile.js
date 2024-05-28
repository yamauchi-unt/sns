// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // セッションストレージのトークン有無判定
    TokenManager.hasTokenCheck();

    // 現在のユーザ名取得
    loadUserName();

    // フォーム取得
    const form = document.querySelector('form');

    // 変更するボタン押下イベント
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // 変更するボタン押下後の処理呼び出し
        profileFormSubmit();
    });
});

/**
 * プロフィール編集
 */
function profileFormSubmit() {
    // 入力値
    const inputs = {
        user_name: document.getElementById('userName').value.trim(),
        current_password: document.getElementById('currentPass').value.trim(),
        new_password: document.getElementById('newPass1').value.trim(),
        new_password_confirm: document.getElementById('newPass2').value.trim(),
    };

    // バリデーションルール
    const rules = {
        user_name: { required: true , max: 30 },
        current_password: { dependent: ['new_password', 'new_password_confirm'] },
        new_password: { dependent: ['current_password', 'new_password_confirm'] },
        new_password_confirm: { dependent: ['current_password', 'new_password'], match: 'new_password' },
    };

    // エラーメッセージ表示領域
    const errorFields = {
        user_name: 'errorUserName',
        current_password: 'errorCurrentPass',
        new_password: 'errorNewPass1',
        new_password_confirm: 'errorNewPass2',
    };

    // バリデーション実行
    const validator = new Validator(inputs, rules, errorFields);
    // バリデーションエラーがある場合、リターン
    if (!validator.validate()) {
        return;
    }

    // 送信データ
    const postData = {
        user_name: inputs.user_name,
        current_password: inputs.current_password,
        new_password: inputs.new_password,
    };

    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}myprofile`, {
        method: 'PATCH',
        headers: {
            'Authorization': `Bearer ${token}` ,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(postData)
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                alert('プロフィール編集が完了しました。');
                window.location.href = 'mypage.html';
                break;
            case 422:
                return response.json().then(data => {
                    throw { status: response.status, data };
                });
            default:
                throw new Error(response.status);
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
                TokenManager.removeToken();
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
            } else {
                window.location.href = '500.html';
            }
        }
    });
}