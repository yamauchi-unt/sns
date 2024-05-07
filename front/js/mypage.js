let nextPageUrl = null;
// ロード状態を追跡
let isLoading = false;

// ページ読み込みイベント
document.addEventListener('DOMContentLoaded', function() {
    // マイページ用のエンドポイント
    let apiEndpoint = `${API_BASE_URL}myposts`;

    // マイ投稿ID一覧取得
    loadPostIds(apiEndpoint);

    // 画面スクロールイベント
    window.addEventListener('scroll', () => {
        const viewportHeight = window.innerHeight;
        const scrollY = window.scrollY;
        const documentHeight = document.documentElement.offsetHeight;

        // 画面最下部までスクロール & 次ページがある & ロード中ではない場合
        if (viewportHeight + scrollY >= documentHeight - 10 && nextPageUrl && !isLoading) {
            console.log("Reached bottom of page");
            // ロード中
            isLoading = true;
            // 次ページの投稿ID一覧取得
            loadPostIds(nextPageUrl);
        }
    });

    // ログアウトボタン押下イベント
    const logoutBtn = document.getElementById('logoutBtn');
    logoutBtn.addEventListener('click', function() {
        if (confirm('ログアウトしますか？')) {
            logout();
        }
    });
});

/**
 * 投稿ID一覧取得
 */
function loadPostIds(url) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    fetch(url, {
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
        // 次ページURL取得
        nextPageUrl = data.next_page_url;
        // 各投稿IDの詳細データを取得する非同期処理の配列を作成
        const detailsPromises = data.data.map(post => loadPostDetails(post.id));
        // すべての投稿データが取得できた後に画面に表示
        Promise.all(detailsPromises).then(posts => {
            posts.forEach(postData => {
                displayPost(postData);
            });
        });
        // ロード終了
        isLoading = false;
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * 投稿詳細取得
 * @param {string} postId
 * @return {Promise<Object>}
 */
function loadPostDetails(postId) {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエスト送信
    return fetch(`${API_BASE_URL}posts/${postId}`, {
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
                window.location.href = '500.html';
        }
    })
    // 例外処理
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    });
}

/**
 * 投稿表示
 * @param {Object} postData
 */
function displayPost(postData) {
    // 投稿コンテナ取得
    const postsContainer = document.getElementById('postsContainer');

    // 日付を YYYY/MM/DD 形式に変換
    const postedDate = new Date(postData.post_date).toLocaleDateString('ja-JP');
    // 日付を YYYY-MM-DD 形式に変換
    const datetime = postData.post_date;

    // Bootstrap用cardのHTMLを生成
    const postCard = document.createElement('div');
    postCard.className = 'card';

    // cardの内容を設定
    postCard.innerHTML = `
        <p class="poster-name">${postData.user_name}</p>
        <a href="post-detail.html?postId=${postData.post_id}">
            <img src="${postData.image}" class="card-img-top" alt="投稿画像">
        </a>
        <div class="card-body">
            <p class="card-text">
                <small class="text-muted">
                    <time datetime="${datetime}">${postedDate}</time>
                </small>
            </p>
        </div>
    `;

    // 投稿コンテナにcardを追加
    postsContainer.appendChild(postCard);
}

function logout() {
    // セッションストレージからトークンを取得
    const token = TokenManager.getToken();

    // リクエストを送信
    fetch(`${API_BASE_URL}auth/token`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    })
    // レスポンス ステータスコード確認
    .then(response => {
        switch (response.status) {
            case 204:
                // トークンをセッションストレージから削除
                TokenManager.removeToken();
                alert('ログアウトしました。');
                window.location.href = 'login.html';
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