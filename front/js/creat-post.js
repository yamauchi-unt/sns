// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    // submitボタン押下
    form.addEventListener('submit', function(event) {
        // フォームのデフォルト送信防止
        event.preventDefault();
        // submitボタン押下後の処理呼び出し
        postFormSubmit();
    });
});

/**
 * 投稿フォームの処理
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
    ImageBase64Converter.encode(inputs.image).then(imageStr => {
        // 送信するデータ
        const postData = {
            image: imageStr,
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