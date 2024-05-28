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
 * URLからエスケープ処理後のpostIdを取得
 */
function getPostIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('postId');
    return encodeURIComponent(postId);
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
            default:
                throw new Error(response.status);
        }
    })
    // レスポンスボディを処理
    .then(postData => {
        console.log(postData);
        // 投稿詳細表示
        displayPostDetail(postData);
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else
        if (error.message.includes('404')) {
            window.location.href = '404.html';
        } else {
            window.location.href = '500.html';
        }
    });
}

/**
 * 投稿詳細表示
 * @param {Object} postData
 */
function displayPostDetail(postData) {
    // 投稿コンテナ取得
    const postContainer = document.getElementById('postContainer');
    postContainer.innerHTML = '';

    // 投稿者名と削除ボタンのコンテナ
    const headerDiv = document.createElement('div');
    headerDiv.className = 'd-flex justify-content-between align-items-center';

    // 投稿者名
    const posterName = document.createElement('p');
    posterName.id = 'posterName';
    posterName.className = 'poster-name';
    posterName.textContent = postData.user_name;
    headerDiv.appendChild(posterName);

    // 削除ボタン
    if (postData.mine_frg) {
        const deleteBtn = document.createElement('a');
        deleteBtn.href = "#";
        deleteBtn.id = 'pDeleteBtn';
        deleteBtn.className = 'bi bi-trash text-dark px-3';
        headerDiv.appendChild(deleteBtn);
    }

    // 画像
    const image = document.createElement('img');
    image.src = postData.image;
    image.id = 'postedImage';
    image.className = 'card-img-top';
    image.alt = '投稿画像';

    // カード本体
    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';

    // 投稿テキスト
    const postedStr = document.createElement('p');
    postedStr.id = 'postedStr';
    postedStr.className = 'card-text mb-1';
    postedStr.innerHTML = postData.message.replace(/\n/g, '<br>'); // 改行を<br>に変換

    // コメント数
    const commentCount = document.createElement('a');
    commentCount.href = "#";
    commentCount.id = 'commentCount';
    commentCount.className = 'text-decoration-none text-dark';
    commentCount.setAttribute('data-bs-toggle', 'modal');
    commentCount.setAttribute('data-bs-target', '#staticBackdrop');
    commentCount.textContent = `コメントを見る`;

    // コメント数を囲む小さいテキストの要素
    const small1 = document.createElement('small');
    small1.className = 'text-body-secondary';
    small1.appendChild(commentCount);

    // コメント数を含むカードテキスト要素
    const cardText1 = document.createElement('div');
    cardText1.className = 'card-text mb-1';
    cardText1.appendChild(small1);

    // 日付
    const postDate = document.createElement('time');
    postDate.setAttribute('datetime', postData.post_date);
    postDate.textContent = new Date(postData.post_date).toLocaleDateString('ja-JP');

    // 日付を囲む小さいテキストの要素
    const small2 = document.createElement('small');
    small2.className = 'text-body-secondary';
    small2.appendChild(postDate);

    // 日付を含むカードテキスト要素
    const cardText2 = document.createElement('div');
    cardText2.className = 'card-text mb-1';
    cardText2.appendChild(small2);

    // コンテナに要素を追加
    postContainer.appendChild(headerDiv);
    postContainer.appendChild(image);
    cardBody.appendChild(postedStr);
    cardBody.appendChild(cardText1);
    cardBody.appendChild(cardText2);
    postContainer.appendChild(cardBody);

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
    if (commentCount) {
        let apiEndpoint = `${API_BASE_URL}posts/${postData.post_id}/comments`;
        commentCount.addEventListener('click', function(event) {
            // コメントモーダルのタイトルをリセット
            const modalTitle = document.querySelector('#staticBackdrop .modal-title');
            modalTitle.textContent = 'コメント';
            // コメントコンテナを取得、内容をクリア
            const commentsContainer = document.getElementById('commentsContainer');
            commentsContainer.innerHTML = '';
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
            default:
                throw new Error(response.status);
        }
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else
        if (error.message.includes('403')) {
            alert('削除権限がありません。');
        } else {
            window.location.href = '500.html';
        }
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
            default:
                throw new Error(response.status);
        }
    })
    // レスポンスボディを処理
    .then(data => {
        console.log(data);
        // 次ページURL取得
        nextPageUrl = data.next_page_url;
        // コメント表示
        displayComments(data);
        // ロード終了
        isLoading = false;
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else {
            window.location.href = '500.html';
        }
    });
}

/**
 * コメント表示
 * @param {Object} commentsData
 */
function displayComments(commentsData) {
    // コメントモーダルタイトル
    const modalTitle = document.querySelector('#staticBackdrop .modal-title');
    modalTitle.textContent = `コメント${commentsData.total}件`;

    // コメントコンテナ取得
    const commentsContainer = document.getElementById('commentsContainer');

    if (commentsData.total === 0 && commentsContainer.innerHTML === '') {
        commentsContainer.innerHTML = '<p>コメントはありません。</p>';
        return;
    }
    // 各コメント表示
    commentsData.data.forEach(comment => {
        // コメント全体のコンテナ
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-info';

        // ユーザー名と削除ボタンのコンテナえｇ
        const headerDiv = document.createElement('div');
        headerDiv.className = 'd-flex align-items-center justify-content-between';

        // ユーザー名
        const usernameP = document.createElement('p');
        usernameP.className = 'comment-sender mb-1';
        usernameP.textContent = comment.user_name;
        headerDiv.appendChild(usernameP);

        // 削除ボタン
        if (comment.mine_frg) {
            const deleteBtn = document.createElement('a');
            deleteBtn.href = "#";
            deleteBtn.className = 'bi bi-trash text-dark cDeleteBtn';
            deleteBtn.setAttribute('comment-id', comment.comment_id);
            headerDiv.appendChild(deleteBtn);
        }

        // コメントテキスト
        const commentTextP = document.createElement('p');
        commentTextP.className = 'comment-text';
        commentTextP.textContent = comment.comment;
        const errorComment = document.getElementById('errorComment');
        errorComment.textContent = '';

        // 要素を組み立て
        commentElement.appendChild(headerDiv);
        commentElement.appendChild(commentTextP);

        // コメントコンテナに追加
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
            default:
                throw new Error(response.status);
        }
    })
    // 例外処理
    .catch(error => {
        console.error('Error:', error.message);
        if (error.message.includes('400')) {
            window.location.href = '400.html';
        } else
        if (error.message.includes('401')) {
            alert('再度ログインしてください。');
            window.location.href = 'login.html';
        } else
        if (error.message.includes('403')) {
            alert('削除権限がありません。');
        } else {
            window.location.href = '500.html';
        }
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
                alert('再度ログインしてください。');
                window.location.href = 'login.html';
            } else
            if (error.message.includes('404')) {
                window.location.href = '404.html';
            } else {
                window.location.href = '500.html';
            }
        }
    });
}
