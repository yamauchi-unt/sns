// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    loadUserName();
    const form = document.querySelector('form');

    // submitボタン押下イベント
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // submitボタン押下後の処理呼び出し
        profileFormSubmit();
    });
});

/**
 * プロフィール取得取得
 */
function loadUserName() {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}myprofile`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}` ,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                return response.json();
            default:
                window.location.href = '500.html';
        }
    })
    // レスポンスボディを処理
    .then(data => {
        console.log(data);
        console.log(data.user_name);
        setValueToElement('userName', data.user_name);
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

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
        // current_password: { dependent: 'new_password' },
    };

    // エラーメッセージ表示領域
    const errorFields = {
        user_name: 'errorUserName',
        current_password: 'errorCurrentPass',
        // new_password: 'errorNewPass1',
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