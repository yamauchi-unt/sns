// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // セッションストレージのトークン有無判定
    TokenManager.hasTokenCheck();

    // フォーム取得
    const form = document.querySelector('form');

    // 投稿ボタン押下
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // 投稿ボタン押下後の処理呼び出し
        postFormSubmit();
    });
});

/**
 * 投稿ボタン押下後の処理
 */
function postFormSubmit() {
    // 入力値
    const inputs = {
        image: document.getElementById('image').files[0],
        message: document.getElementById('message').value.trim(),
    };

    // バリデーションルール
    const rules = {
        image: { required: true },
        message: { required: true },
    };

    // エラーメッセージ表示領域
    const errorFields = {
        image: 'errorImage',
        message: 'errorMessage',
    };

    // バリデーション実行
    const validator = new Validator(inputs, rules, errorFields);
    // バリデーションエラーがある場合、リターン
    if (!validator.validate()) {
        return;
    }

    // 画像のエンコード文字列を非同期で取得
    ImageBase64Encode(inputs.image).then(base64ImageStr => {
        // 送信するデータ
        const postData = {
            image: base64ImageStr,
            message: inputs.message,
        };

        // セッションストレージからトークンを取得
        const token = TokenManager.getToken();

        // リクエスト送信
        fetch(`${API_BASE_URL}posts`, {
            method: 'POST',
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
                case 201:
                    alert('投稿が完了しました。');
                    window.location.href = 'timeline.html';
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
    });
}

/**
 * 画像をBase64でエンコード
 * @param {File} file
 * @return {Promise<string>}
 */
function ImageBase64Encode(file) {
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

/*
 * プレビュー表示
 */
function previewImage() {
    const fileInput = document.getElementById('image');
    const file = fileInput.files[0];
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imagePreview = document.createElement('img');
            imagePreview.src = e.target.result;
            imagePreview.classList.add('img-fluid');
            imagePreviewContainer.innerHTML = '';
            imagePreviewContainer.appendChild(imagePreview);
        };
        reader.readAsDataURL(file);
    }
}