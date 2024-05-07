let nextPageUrl = null;
// ロード状態を追跡
let isLoading = false;

// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // セッションストレージのトークン有無判定
    TokenManager.hasTokenCheck();

    // URLから投稿ID取得
    const postId = getPostIdFromUrl();
    // 投稿詳細取得
    loadPostDetail(postId);
});

/**
 * URLからpostIdを取得
 */
function getPostIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('postId');
}

/**
 * 投稿詳細取得
 * @param {number} postId
 */
function loadPostDetail(postId) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}posts/${postId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                return response.json();
            case 401:
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
                break;
            case 404:
                window.location.href = '404.html';
                break;
            default:
                window.location.href = '500.html';
        }
    })
    // レスポンスボディを処理
    .then(postData => {
        console.log(postData);
        displayPostDetail(postData);
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * 投稿詳細表示
 * @param {Object} postData
 */
function displayPostDetail(postData) {
    // 投稿コンテナ取得
    const postContainer = document.getElementById('postContainer');
    // 投稿文
    const message = postData.message.replace(/\n/g, '<br>');
    // 日付を YYYY/MM/DD 形式に変換
    const date = new Date(postData.post_date).toLocaleDateString('ja-JP');

    postContainer.innerHTML = `
    <div class="d-flex justify-content-between align-items-center">
        <p id="posterName" class="poster-name">${postData.user_name}</p>
        ${postData.mine_frg ? '<a href="#" id="pDeleteBtn" class="bi bi-trash text-dark px-3"></a>' : ''}
    </div>
    <img src="${postData.image}" id="postedImage" class="card-img-top" alt="">
    <div class="card-body">
        <p id="postedStr" class="card-text mb-1">${message}</p>
        <div class="card-text mb-1">
            <small class="text-body-secondary">
                <a href="#" id="commentCount" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                コメント${postData.comment_count}件をすべて見る
                </a>
            </small>
        </div>
        <div class="card-text mb-1">
            <small class="text-body-secondary">
                <time id="postedDate" datetime="${postData.post_date}">${date}</time>
            </small>
        </div>
    </div>
    `;

    // 投稿削除ボタン押下イベント
    const pDeleteBtn = document.getElementById('pDeleteBtn');
    if (pDeleteBtn) {
        pDeleteBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('投稿を削除してよろしいですか？')) {
                deletePost(postData.post_id);
            }
        });
    }

    // コメント件数リンク押下イベント
    const commentCount = document.getElementById('commentCount');
    if (commentCount) {
        let apiEndpoint = `${API_BASE_URL}posts/${postData.post_id}/comments`;
        commentCount.addEventListener('click', function(event) {
            // コメント取得
            loadComments(apiEndpoint);
        });

        const modalBody = document.querySelector('#staticBackdrop .modal-body');
        if (modalBody) {
            // モーダルのスクロールイベント
            modalBody.addEventListener('scroll', function() {
                const scrollPosition = modalBody.scrollTop + modalBody.clientHeight;
                const modalContentHeight = modalBody.scrollHeight;
        
                // 画面最下部までスクロール & 次ページがある & ロード中ではない場合
                if (scrollPosition >= modalContentHeight - 10 && nextPageUrl && !isLoading) {
                    console.log("Reached bottom of page");
                    // ロード中
                    isLoading = true;
                    // 次ページのコメント取得
                    loadComments(nextPageUrl);
                }
            });

            // コメント削除ボタン押下イベント
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('cDeleteBtn')) {
                    event.preventDefault();
                    const commentId = event.target.getAttribute('comment-id');
                    if (commentId && confirm('コメントを削除してよろしいですか？')) {
                        // コメント削除
                        deleteComment(commentId);
                    }
                }
            });

            // コメント送信submitボタン押下イベント
            document.addEventListener('submit', function(event) {
                // フォームのデフォルト送信防止
                event.preventDefault();
                // コメント送信
                commentFormSubmit(postData.post_id);
            });
        }
    }
}

/**
 * 投稿を削除する
 * @param {number} postId
 */
function deletePost(postId) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}posts/${postId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 204:
                alert('投稿が削除されました。');
                window.location.href = 'timeline.html';
                break;
            case 401:
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
                break;
            case 403:
                alert('削除権限がありません。');
                break;
            default:
                window.location.href = '500.html';
        }
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * コメント取得
 * @param {string} apiEndpoint
 */
function loadComments(apiEndpoint) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(apiEndpoint, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 200:
                return response.json();
            case 401:
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
                break;
            default:
                window.location.href = '500.html';
        }
    })
    // レスポンスボディを処理
    .then(data => {
        console.log(data);
        // 投稿0件の場合
        if (data.total === 0) {
            const commentsContainer = document.getElementById('commentsContainer');
            commentsContainer.innerHTML = '<p>コメントはありません。</p>';
            return;
        }
        // 次ページURL取得
        nextPageUrl = data.next_page_url;
        // コメント表示
        displayComments(data);
        // ロード終了
        isLoading = false;
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * コメント表示
 * @param {Object} commentsData
 */
function displayComments(commentsData) {
    // コメントコンテナ取得
    const commentsContainer = document.getElementById('commentsContainer');
    // 各コメント表示
    commentsData.data.forEach(comment => {
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-info';
        commentElement.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <p class="comment-sender mb-1">${comment.user_name}</p>
                ${comment.mine_frg ? `<a href="#" class="bi bi-trash text-dark cDeleteBtn" comment-id="${comment.comment_id}"></a>` : ''}
            </div>
            <p class="comment-text">${comment.comment}</p>
        `;
        commentsContainer.appendChild(commentElement);
    });
}

/**
 * コメント削除
 * @param {number} commentId
 */
function deleteComment(commentId) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(`${API_BASE_URL}comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 204:
                alert('コメントが削除されました。');
                window.location.reload();
                break;
            case 401:
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
                break;
            case 403:
                alert('削除権限がありません。');
                break;
            default:
                window.location.href = '500.html';
        }
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * コメントフォーム送信
 * @param {number} postId
 */
function commentFormSubmit(postId) {
    // 入力値
    const inputs = {
        comment: document.getElementById('comment').value.trim(),
    };

    // バリデーションルール
    const rules = {
        comment: { required: true , max: 255 },
    };

    // エラーメッセージ表示領域
    const errorFields = {
        comment: 'errorComment',
    };

    // バリデーション実行
    const validator = new Validator(inputs, rules, errorFields);
    // バリデーションエラーがある場合、リターン
    if (!validator.validate()) {
        return;
    }

    // 送信するデータ
    const postData = {
        comment: inputs.comment,
    };

    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // コメントデータをAPIに送信
    fetch(`${API_BASE_URL}posts/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(postData)
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 201:
                alert('コメント送信が完了しました。');
                window.location.reload();
                break;
            case 401:
                alert('再度ログインしてください。');
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
